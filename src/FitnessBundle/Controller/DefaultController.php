<?php

namespace FitnessBundle\Controller;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/fitness", name="fitness")
     */
    public function indexAction()
    {
        /** @var OAuthToken $oauthToken */
        $oauthToken = $this->container->get('security.token_storage')->getToken();

        $result = $this->get('fitness_steps')->getSteps($oauthToken);

        return $this->render(
            'FitnessBundle:Default:index.html.twig',
            ['user' => $oauthToken->getUsername(), 'step_count' => $result]
        );
    }


}
