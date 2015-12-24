<?php

namespace Activity\Controller;

use Activity\Model\Activity;
use Activity\Service\Signup;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as SessionContainer;
use Activity\Form\ActivitySignup as SignupForm;
use Zend\View\Model\ViewModel;

class ActivityController extends AbstractActionController
{
    /**
     * View all activities.
     */
    public function indexAction()
    {
        $activityService = $this->getServiceLocator()->get('activity_service_activity');
        $translatorService = $this->getServiceLocator()->get('activity_service_activityTranslator');
        $session = new SessionContainer('lang');
        $activities = $activityService->getApprovedActivities();
        $translatedActivities = [];
        foreach ($activities as $activity){
            $translatedActivities[] = $translatorService->getTranslatedActivity($activity, $session->lang);
        }
        return ['activities' => $translatedActivities];
    }

    /**
     * View one activity.
     */
    public function viewAction()
    {
        $id = (int) $this->params('id');
        $activityService = $this->getServiceLocator()->get('activity_service_activity');
        $translatorService = $this->getServiceLocator()->get('activity_service_activityTranslator');
        $session = new SessionContainer('lang');

        /** @var $activity Activity*/
        $activity = $activityService->getActivity($id);

        $translatedActivity = $translatorService->getTranslatedActivity($activity, $session->lang);
        $identity = $this->getServiceLocator()->get('user_role');
        /** @var Signup $signupService */
        $signupService = $this->getServiceLocator()->get('activity_service_signup');

        $fields = $translatedActivity->getFields();
        $form = null;
        if ($signupService->isAllowedToSubscribe()) {
            $form = $signupService->getForm($fields);
        }
        $subscriptionDeadLinePassed = $activity->getSubscriptionDeadline() < new \DateTime();
        return [
            'activity' => $translatedActivity,
            'signupOpen' => $activity->getCanSignUp() && !$subscriptionDeadLinePassed,
            'isLoggedIn' => $identity !== 'guest',
            'isSignedUp' => $identity !== 'guest' && $signupService->isSignedUp($translatedActivity, $identity->getMember()),
            'signedUp' => $signupService->getSignedUpUsers($translatedActivity),
            'signupData' => $translatorService->getTranslatedSignedUpData($activity, $session->lang),
            'form' => $form,
            'fields' => $fields
        ];
    }

    /**
     * Create an activity.
     */
    public function createAction()
    {
        $activityService = $this->getServiceLocator()->get('activity_service_activity');
        $form = $activityService->getForm();
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $form->setData($postData);

            if ($form->isValid()) {
                $activity = $activityService->createActivity(
                    $form->getData(\Zend\Form\FormInterface::VALUES_AS_ARRAY),
                    $postData['language_dutch'],
                    $postData['language_english']
                );

                $this->redirect()->toRoute('activity/view', [
                    'id' => $activity->getId(),
                ]);
            }
        }

        return ['form' => $form];
    }

    /**
     * Signup for a activity.
     */
    public function signupAction()
    {
        $id = (int) $this->params('id');
        $activityService = $this->getServiceLocator()->get('activity_service_activity');
        /** @var \Activity\Service\Signup $signupService */
        $signupService = $this->getServiceLocator()->get('activity_service_signup');

        /** @var  $activity Activity */
        $activity = $activityService->getActivity($id);

        $translator = $activityService->getTranslator();

        $params = $this->viewAction();
        //Assure the form is used
        if (!$this->getRequest()->isPost()){
            $params['error'] = $translator->translate('Use the form to subscribe');
            return $params;
        }

        $subscriptionDeadLinePassed = $activity->getSubscriptionDeadline() < new \DateTime();

        // Assure you can sign up for this activity
        if (!$activity->getCanSignup() || $subscriptionDeadLinePassed) {
            $params['error'] = $translator->translate('You can not subscribe to this activity at this moment');
            return $params;
        }

        if (!$signupService->isAllowedToSubscribe()) {
            $params['error'] = $translator->translate('You need to log in to subscribe');
            return $params;
        }

        $form = $signupService->getForm($activity->getFields());
        $form->setData($this->getRequest()->getPost());

        //Assure the form is valid
        if (!$form->isValid()){
            $params['error'] = $translator->translate('Wrong form');
            return $params;
        }

        $identity = $this->getServiceLocator()->get('user_role');
        $user = $identity->getMember();

        if ($signupService->isSignedUp($activity, $user)) {
            $params['error'] = $translator->translate('You have already been subscribed for this activity');
        } else {
            $signupService->signUp($activity, $form->getData(\Zend\Form\FormInterface::VALUES_AS_ARRAY));
            $params['success'] = true;
        }

        return $params;
    }

    /**
     * Signup for a activity.
     */
    public function signoffAction()
    {
        $id = (int) $this->params('id');
        /** @var \Activity\Service\Activity $activityService */
        $activityService = $this->getServiceLocator()->get('activity_service_activity');
        /** @var \Activity\Service\SignUp $signupService */
        $signupService = $this->getServiceLocator()->get('activity_service_signup');

        $activity = $activityService->getActivity($id);
        $translator = $activityService->getTranslator();

        if (!$signupService->isAllowedToSubscribe()) {
            $params['error'] = $translator->translate('You have to be logged in to subscribe for this activity');

            return $params;
        }

        $identity = $this->getServiceLocator()->get('user_role');
        $user = $identity->getMember();

        if (!$signupService->isSignedUp($activity, $user)) {
            $params['error'] = $translator->translate('You are not subscribed for this activity!');
        } else {
            $signupService->signOff($activity, $user);
            $params['success'] = true;
        }
        $params = $this->viewAction();

        return $params;
    }

    public function touchAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setVariables(array('key' => 'value'))
            ->setTerminal(true);

        return $viewModel;
    }
}
