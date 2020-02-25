<?php

namespace VeloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use VeloBundle\Entity\Evenement;
use VeloBundle\Entity\Commentaire;
use VeloBundle\Entity\Participant;
use VeloBundle\Form\EvenementType;
use VeloBundle\Form\CommentaireType;
use VeloBundle\Form\ParticipantType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EvenementController extends Controller
{
    public function AjouterAction(Request $request)
    {
        $evenement = new Evenement();
        $message="";
        $form =$this->createForm(EvenementType::class,$evenement);
        $form->handleRequest($request);

        if($form->isValid() && $form->get('datedebut')->getData() < $form->get('datefin')->getData()){
            $em=$this->getDoctrine()->getManager();
            $evenement->uploadProfilePicture();
            $em->persist($evenement);
            $em->flush();
            //return $this->redirectToRoute('gf_affiche');
        }else{
            $message="date debut est superieur a date fin";
            $this->addFlash('success', $message);

            return $this->render('@Velo/evenement/AjouterEve.html.twig', array('form'=>$form->createView(),'mess'=>$message));
        }
        return $this->render('@Velo/evenement/AjouterEve.html.twig', array('form'=>$form->createView()));
    }

    public function afficheAction(){
        $em=$this->getDoctrine()->getManager();
        $evenement=$em->getRepository("VeloBundle:Evenement")->findAll();
        return $this->render('@Velo/evenement/AfficheEve.html.twig', array('evenements'=>$evenement));
    }

    public  function updateAction(Request $request,$id){
        $em=$this->getDoctrine()->getManager();
        $evenement=$em->getRepository(Evenement::class)->find($id);
        $form=$this->createForm(EvenementType::class,$evenement);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em->persist($evenement);
            $em->flush();
            return $this->redirectToRoute('evenement_afficher');
        }
        return $this->render('@Velo/evenement/UpdateEve.html.twig', array('form'=>$form->createView()));


    }


    public function supprimerAction($id){
        $em=$this->getDoctrine()->getManager();
        $evenement=$em->getRepository(Evenement::class)->find($id);
        $em->remove($evenement);
        $em->flush();
        return $this->redirectToRoute('evenement_afficher');
    }


    public function evenementDetailsAction(Evenement $evenement,Request $request){

        $em=$this->getDoctrine()->getManager();
        $participant=new Participant();
        $con=$request->get('id');
        $evenement=$em->getRepository(Evenement::class)->find($con);
        $form =$this->createForm(ParticipantType::class,$participant);
        $form->handleRequest($request);
        if($form->isValid()){
            $em=$this->getDoctrine()->getManager();
            //$category->uploadProfilePicture();
            $participant->setEvenement($evenement);
            $em->persist($participant);
            $em->flush();
            //return $this->redirectToRoute('gf_affiche');
        }

        $participants=$em->getRepository(Participant::class)->findAll();


        return $this->render('@Velo/evenement/evenementdetail.html.twig', [


            'evenement'=>$evenement,'form'=>$form->createView(),'participants'=>$participants
        ]);
    }

    public function exportAction(){
        $em=  $this->getDoctrine()->getManager();
        $evenements=$em->getRepository(Evenement::class)->findAll();
        $writer = $this->container->get('egyg33k.csv.writer');
        $csv = $writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne(['id', 'nom']);

        foreach ($evenements as $evenement){
            $csv->insertOne([$evenement->getId(), $evenement->getLibelle()]);
        }
        $csv->output('evenements.csv');

        die('export');
    }


    public function affichefrontAction(){
        $em=$this->getDoctrine()->getManager();
        $evenement=$em->getRepository("VeloBundle:Evenement")->findAll();
        return $this->render('@Velo/evenement/AfficheEvefront.html.twig', array('evenements'=>$evenement));
    }

    
    public function evenementDetailsFrontAction(Evenement $evenement,Request $request){

        $em=$this->getDoctrine()->getManager();
        $commentaire=new Commentaire();
        $commentaire->setDatecreate(new \DateTime('now'));
        $con=$request->get('id');
        $evenement=$em->getRepository(Evenement::class)->find($con);
        $form =$this->createForm(CommentaireType::class,$commentaire);
        $form->handleRequest($request);
        if($form->isValid()){

            $em=$this->getDoctrine()->getManager();
            //$category->uploadProfilePicture();

            $commentaire->setEvenement($evenement);

            $em->persist($commentaire);
            $em->flush();
            //return $this->redirectToRoute('gf_affiche');
        }
        $commentaires=$em->getRepository(Commentaire::class)->findAll();
        $participants=$em->getRepository(Participant::class)->findAll();

      //  $livres=$em->getRepository(Livre::class)->findAll();


        return $this->render('@Velo/evenement/evenementdetailfront.html.twig', [


            'evenement'=>$evenement,'form'=>$form->createView(),'commentaires'=>$commentaires,'participants'=>$participants
        ]);
    }

    public function evenement_calendrierAction()
    {

        return $this->render('@Velo/evenement/evenement_calendrier.html.twig');
    }



    public function JsonCalSEAction()
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $cal = $em->getRepository("VeloBundle:Evenement")->findAll();

        foreach ($cal as $row) {

            $data[] = array(
                'id' => $row->getid(),
                'title' => $row->getLibelle(),
                'start' => $row->getDatedebut()->format('Y-m-d H:m:s'),
                'end' => $row->getDatefin()->format('Y-m-d H:m:s')

            );
        }
        $response=new Response();
        $content = json_encode($data);
        $response->setContent($content);
        return $response;
    }

    public function supprimerPAction($id,Request $request){
        $em=$this->getDoctrine()->getManager();
        $con=$request->get('id');
        $evenement=$em->getRepository(Evenement::class)->find($con);
        $participant=$em->getRepository(Participant::class)->find($id);
        $participant->setEvenement($evenement);

        $em->remove($participant);
        $em->flush();
        return $this->redirectToRoute('evenement_afficher');
    }
}
