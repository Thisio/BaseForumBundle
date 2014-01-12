<?php

/**
 * This controller is meant to be used for demonstration
 * Ultimately, you will want to use these snippets in your system.
 *
 * If you want to try these actions, include the routing file of this bundle in your
 * installation.
 * Example in app/config/routing_dev.yml
 *
 * TeapotBaseForumBundle:
 *     resource: "@TeapotBaseForumBundle/Resources/config/routing.yml"
 *     prefix:   /forum-test
 */

namespace Teapot\Base\ForumBundle\Controller;

use Teapot\Base\ForumBundle\Entity\Board;
use Teapot\Base\ForumBundle\Form\CreateBoardType;

use Teapot\Base\ForumBundle\Entity\Topic;
use Teapot\Base\ForumBundle\Form\CreateTopicType;

use Teapot\Base\ForumBundle\Entity\Message;
use Teapot\Base\ForumBundle\Form\CreateMessageType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModuleController extends Controller
{

    public function listTopicsAction($boardId = null)
    {
        $em = $this->get('doctrine')->getManager();

        if ($boardId === null) {
            $topics = $this->get('teapot.forum.topic')->getLatestTopics(0, 10);
        }
        else {
            $board = $em->getRepository($this->getParameter('teapot_forum.board_repository.class'))->find($boardId);

            if (!$board instanceof Board) {
                throw $this->createNotFoundException("No Board Found");
            }

            $topics = $this->get('teapot.forum.topic')->getLatestTopicsByBoard($board, 0, 10);
        }

        return $this->render('TeapotBaseForumBundle:Module:listTopics.html.twig', array(
            'topics' => $topics
        ));
    }

    public function listBoardsAction()
    {
        $em = $this->get('doctrine')->getManager();

        return $this->render('TeapotBaseForumBundle:Module:listBoards.html.twig', array(
        ));
    }

    public function listMessagesAction($topicId)
    {
        $em = $this->get('doctrine')->getManager();

        $topic = $em->getRepository($this->getParameter('teapot_forum.topic_repository.class'))->find($topicId);

        if (!$topic instanceof Topic) {
            throw $this->createNotFoundException("No Topic Found");
        }

        $messages = $this->get('teapot.forum.message')->getLatestMessagesByTopic($topic, 0, 10);

        return $this->render('TeapotBaseForumBundle:Module:listMessages.html.twig', array(
            'messages' => $messages
        ));
    }

    public function newMessageAction($topicId)
    {
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') === false) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $em = $this->get('doctrine')->getManager();

        $topic = $em->getRepository($this->getParameter('teapot_forum.topic_repository.class'))->find($topicId);

        if (!$topic instanceof Topic) {
            throw $this->createNotFoundException("No Topic Found");
        }

        $request = $this->get('request');

        $message = new Message();

        $form = $this->createForm(new CreateMessageType(), $message);

        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if ($form->isValid() === true) {
                $message->setTopic($topic);
                $this->get('teapot.forum.message')->save($message);
            }
        }

        return $this->render('TeapotBaseForumBundle:Module:newMessage.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function newTopicAction($boardId)
    {
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') === false) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $em = $this->get('doctrine')->getManager();

        $board = $em->getRepository($this->getParameter('teapot_forum.board_repository.class'))->find($boardId);

        if (!$board instanceof Board) {
            throw $this->createNotFoundException("No Board Found");
        }

        $request = $this->get('request');

        $topic = new Topic();

        $form = $this->createForm(new CreateTopicType(), $topic);

        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if ($form->isValid() === true) {
                $user = $this->get('security.context')->getToken()->getUser();

                $topic->setBoard($board);

                $this->get('teapot.forum.topic')->save($topic);

                $message = new Message();

                $message->setBody($form['body']->getData());
                $message->setTopic($topic);

                $this->get('teapot.forum.message')->save($message);
            }
        }

        return $this->render('TeapotBaseForumBundle:Module:newTopic.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function newBoardAction()
    {
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') === false) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $request = $this->get('request');

        $board = new Board();

        $form = $this->createForm(new CreateBoardType(), $board);

        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if ($form->isValid() === true) {
                $this->get('teapot.forum.board')->save($board);
            }
        }

        return $this->render('TeapotBaseForumBundle:Module:newBoard.html.twig', array(
            'form' => $form->createView()
        ));
    }
}