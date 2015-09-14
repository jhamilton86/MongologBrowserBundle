<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Controller;

use Mongolog\Bundle\MongologBrowserBundle\Form\LogSearchType;
use Mongolog\Bundle\MongologBrowserBundle\Model\DateRangeSet;
use Mongolog\Bundle\MongologBrowserBundle\Model\LogRepository;
use MongoClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class DefaultController extends Controller
{
    private function getDateRangeFromQueryParams(array $params)
    {
        return new DateRangeSet(new \DateTime($params['datefrom']), new \DateTime($params['dateto']));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        $connection = $this->getMongoConnection();

        $filter = $this->get('form.factory')->create(new LogSearchType($connection, 'logs', 'logs'), null, array(
            'log_levels'    => $this->getLogRepository()->getLogsLevel(),
        ));

        try {

            $logsPerPage = $this->container->getParameter('mongolog_browser.logs_per_page');
            $page = $request->get('page', 1);

            if($search = $request->get($filter->getName()))
            {
                $filter->submit($search);

                $validParams = $filter->getData();

                $query = $this->getLogRepository()->search(
                    $page,
                    $logsPerPage,
                    $this->getDateRangeFromQueryParams($validParams),
                    $validParams['term'],
                    $validParams['level']);
            }
            else
            {
                $query = $this->getLogRepository()->all($page, $logsPerPage);

            }

            $pagination = $this->get('knp_paginator')->paginate(
                array(),
                $page,
                $logsPerPage
            );

            $pagination->setTotalItemCount($query['total']);

        } catch (\MongoException $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            $pagination = array();
        }

        return $this->render('MongologBrowserBundle:Default:index.html.twig', array(
            'filter'      => $filter->createView(),
            'pagination'  => $pagination,
            'results'     => isset($query['results']) ? $query['results'] : array(),
            'base_layout' => $this->getBaseLayout(),
        ));
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction(Request $request, $id)
    {
        $log = $this->getLogRepository()->getLogById($id);

        if (null === $log) {
            throw $this->createNotFoundException('The log entry does not exist');
        }

        return $this->render('MongologBrowserBundle:Default:show.html.twig', array(
            'log'          => $log,
            'base_layout'  => $this->getBaseLayout(),
        ));
    }

    /**
     * @return string
     */
    protected function getBaseLayout()
    {
        return $this->container->getParameter('mongolog_browser.base_layout');
    }

    /**
     * @return MongoClient
     */
    private function getMongoConnection()
    {
        $server = $this->container->getParameter('mongodb_server');
        $username = $this->container->getParameter('mongodb_username');
        $password = $this->container->getParameter('mongodb_password');
        $db = $this->container->getParameter('mongodb_database');

        return new MongoClient($server, array(
            'username' => $username,
            'password' => $password,
            'db' => $db
        ));
    }

    /**
     * @return \Mongolog\Bundle\MongologBrowserBundle\Model\LogRepository
     */
    protected function getLogRepository()
    {
        $connection = $this->getMongoConnection();

        return new LogRepository($connection, 'logs', 'logs');
    }
}
