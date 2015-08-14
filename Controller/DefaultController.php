<?php

namespace Mongolog\Bundle\MonologBrowserBundle\Controller;

use Mongolog\Bundle\MonologBrowserBundle\Form\LogSearchType;
use Mongolog\Bundle\MonologBrowserBundle\Model\LogRepository;
use MongoClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class DefaultController extends Controller
{

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

                $query = $this->getLogRepository()->search($page, $logsPerPage, $filter->getData());
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
     * @return \Mongolog\Bundle\MonologBrowserBundle\Model\LogRepository
     */
    protected function getLogRepository()
    {
        $connection = $this->getMongoConnection();

        return new LogRepository($connection, 'logs', 'logs');
    }
}
