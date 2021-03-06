<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Individual;
use AppBundle\Entity\Onboard;
use AppBundle\Entity\Profile;
use AppBundle\Form\CorporateForm;
use AppBundle\Form\IndividualForm;
use AppBundle\Form\OnboardForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OnboardController extends Controller
{
    /**
     * @Route("/",name="onboard")
     */

    public function indexAction(Request $request){
        return $this->render('start.htm.twig');
    }
    /**
     * @Route("/start/individual",name="onboard-individual")
     */
    public function individualAction(Request $request)
    {
        $individual = new Individual();
        $individual->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(IndividualForm::class,$individual);
        $form->handleRequest($request);

        if($form->isValid()){
            $onboard = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($onboard);
            $em->flush();

           $this->sendWelcomeEmail($onboard->getfirstName(),$onboard->getEmail(),$onboard->getId());

            return $this->redirectToRoute('onboarded');
        }else{
            $errors = $form->getErrors();
        }

        return $this->render('onboard/onboard.htm.twig',[
            'onboardForm'=>$form->createView()
        ]);
    }
    /**
     * @Route("/start/corporate",name="onboard-corporate")
     */
    public function corporateAction(Request $request)
    {
        $onboard = new Onboard();
        $onboard->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(CorporateForm::class,$onboard);
        $form->handleRequest($request);

        if($form->isValid()){
            $onboard = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($onboard);
            $em->flush();

            //$this->sendWelcomeEmail($onboard->getfirstName(),$onboard->getEmail(),$onboard->getId());

            return $this->redirectToRoute('onboarded');
        }else{
            $errors = $form->getErrors();
        }

        return $this->render('onboard/corporate.htm.twig',[
            'onboardForm'=>$form->createView()
        ]);
    }

    /**
     * @Route("/onboarded",name="onboarded")
     */
    public function onboardedAction(){
        return $this->render('onboard/onboarded.htm.twig');
    }

    public function sendWelcomeEmail($firstName,$emailAddress,$code){
        $message = \Swift_Message::newInstance()
            ->setSubject('KAMP Online Portal Registration')
            ->setFrom('kamp@patchcreate.com','KAMP Online Portal Team')
            ->setTo($emailAddress)
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/onboard.htm.twig
                    'Emails/corporate.htm.twig',
                    array(
                        'name' => $firstName,
                        'code' => $code
                    )
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

}
