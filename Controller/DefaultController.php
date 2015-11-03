<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Controller;

use MongoException;
use Mongolog\Bundle\MongologBrowserBundle\Form\LogSearchType;
use Mongolog\Bundle\MongologBrowserBundle\Model\DateRangeSet;
use Mongolog\Bundle\MongologBrowserBundle\Model\LogRepository;
use MongoClient;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\QueryParserException;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Parser;
use InvalidArgumentException;
use Phlexy\LexingException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InvalidSearchException extends InvalidArgumentException{}

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class DefaultController extends Controller
{
    private function getDateRangeFromQueryParams(array $params)
    {
        return new DateRangeSet(new \DateTime($params['datefrom']), new \DateTime($params['dateto']));
    }

    private function validateResultsPerPage($logsPerPage)
    {
        if ($logsPerPage > 1000)
        {
            throw new InvalidSearchException('Results per page cannot be greater than 1000.');
        }

        return $logsPerPage;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $logRepository = $this->getLogRepository();

        $filter = $this->get('form.factory')->create(new LogSearchType(), null, array(
            'log_levels'    => $logRepository->getLogsLevel(),
        ));

        try {
            $search = $request->get($filter->getName());

            $logsPerPage = (isset($search) && isset($search['results'])) ? $search['results'] : $this->container->getParameter('mongolog_browser.logs_per_page');
            $page = $request->get('page', 1);

            if($search)
            {
                $filter->submit($search);

                $validParams = $filter->getData();

                $mongoQuery = $this->parseSearchQuery($validParams['term']);

                $query = $logRepository->search(
                    $page,
                    $this->validateResultsPerPage($logsPerPage),
                    $this->getDateRangeFromQueryParams($validParams),
                    $mongoQuery,
                    $validParams['level']);
            }
            else
            {
                $query = $logRepository->all($page, $logsPerPage);
            }

            $pagination = $this->get('knp_paginator')->paginate(
                array(),
                $page,
                $logsPerPage
            );

            $pagination->setTotalItemCount($query['total']);

        } catch (MongoException $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            $pagination = array();
        }catch (InvalidSearchException $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            $pagination = array();
        }

        return $this->render('MongologBrowserBundle:Default:index.html.twig', array(
            'filter'      => $filter->createView(),
            'pagination'  => $pagination,
            'results'     => isset($query['results']) ? $query['results'] : array(),
            'base_layout' => $this->getBaseLayout(),
            'search'      => isset($query['search']) ? $query['search'] : array()
        ));
    }

    private function parseSearchQuery($searchQuery)
    {
        $parser = new Parser();

        // Handle a basic message search eg: 'foo bar'
        if($parser->isSimpleLiteral($searchQuery)){
            return array('message' => array('$regex' => $searchQuery));
        }

        // Handle a full query search eg: 'foo > bar'
        $compiler = new Compiler();

        try {
            return $compiler->compile($parser->parse($searchQuery));
        }catch (QueryParserException $e)
        {
            throw new InvalidSearchException(sprintf("Invalid search string '%s' near '%s'", $searchQuery, $e->getMessage()));
        }catch (LexingException $e)
        {
            throw new InvalidSearchException(sprintf("Invalid search string '%s' near '%s'", $searchQuery, $e->getMessage()));
        }
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction(Request $request, $id)
    {
        $logRepository = $this->getLogRepository();

        $log = $logRepository->getLogById($id);

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
     * @return \MongoCollection
     */
    private function getMongoCollection()
    {
        $server = $this->container->getParameter('mongolog_browser.mongo.host');
        $username = $this->container->getParameter('mongolog_browser.mongo.username');
        $password = $this->container->getParameter('mongolog_browser.mongo.password');
        $db = $this->container->getParameter('mongolog_browser.mongo.database');
        $collection = $this->container->getParameter('mongolog_browser.mongo.collection');

        return (new MongoClient($server, array(
            'username' => $username,
            'password' => $password,
            'db' => $db
        )))->selectCollection($db, $collection);
    }

    /**
     * @return \Mongolog\Bundle\MongologBrowserBundle\Model\LogRepository
     */
    protected function getLogRepository()
    {
        return new LogRepository($this->getMongoCollection());
    }
}
