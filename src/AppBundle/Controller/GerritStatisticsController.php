<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GerritProject;
//use AppBundle\Form\Gerrit\ProjectType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GerritStatisticsController extends Controller
{
    const URL = 'http://metrics.andygrunwald.com/api/';

    /**
     *
     * @Route("/statistics/gerrit/activity-monitor/{projectId}", defaults={"projectId" = 59}, name="gerrit-activity-monitor")
     */
    public function activityMonitorAction($projectId, Request $request)
    {
        // TODO What happens if there is no data?
        $projects = $this->getProjects();
        $projects = array_column($projects, 'Name', 'ID');
        $activity = $this->getActivity($projectId);

        list($points, $persons) = $this->prepareActivityData($activity);

        // Form
        // create a task and give it some dummy data for this example
        $gerritProject = new GerritProject();
        $gerritProject->setProjectId($projectId);

        //$form = $this->createForm(new ProjectType(), $gerritProject, ['projects' => $projects]);
        $form = $this->createFormBuilder($gerritProject)
            //->add('projectId', 'text')
            ->add('projectId', 'choice', ['choices' => $projects])
            ->add('save', 'submit', array('label' => 'Generate statistics'))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->redirectToRoute('gerrit-activity-monitor', ['projectId' => $gerritProject->getProjectId()]);
        }

        $templateParams = [
            'projects' => $projects,
            'activity' => [
                'points' => $points,
                'persons' => $persons
            ],
            'form' => $form->createView()
        ];

        return $this->render('statistics/gerrit/activity-monitor.html.twig', $templateParams);
    }

    private function prepareActivityData(array $activity = []) {
        $points = $persons = [];

        foreach ($activity as $row) {
            $yearAndMonth = $row['Year'] . '-' . $row['Month'];

            // Value initialisation
            if (array_key_exists($yearAndMonth, $points) === false) {
                $points[$yearAndMonth] = [];
            }
            if (array_key_exists($row['PersonID'], $points[$yearAndMonth]) === false) {
                $points[$yearAndMonth][$row['PersonID']] = 0;
            }

            $points[$yearAndMonth][$row['PersonID']] += $row['Points'];
            $persons[$row['PersonID']] = $row['Name'];
        }

        // Resort the points :)
        foreach ($points as $yearAndMonth => $pointsForAMonth) {
            arsort($points[$yearAndMonth]);
        }

        return array($points, $persons);
    }

    private function getProjects() {
        $url = self::URL . 'gerrit/projects';
        return $this->getAPIResponse($url);
    }

    private function getActivity($projectId) {
        $url = self::URL . 'gerrit/activity/' . intval($projectId);
        return $this->getAPIResponse($url);
    }

    private function getAPIResponse($url) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Jacobine Frontend'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        // Decode response
        $resp = json_decode($resp, true);

        return $resp;
    }
}
