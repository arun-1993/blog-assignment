<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\DishRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/list", name="list")
     */
    public function list(DishRepository $dishRepository): Response
    {
        $dishes = $dishRepository->findBy(['author' => $this->getUser()->getId()]);

        return $this->render('user/index.html.twig', [
            'dishes' => $dishes
        ]);
    }

    /**
     * @Route("/comments", name="comments")
     */
    public function comments(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(['user' => $this->getUser()->getId()]);

        return $this->render('user/comment.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/comments/edit/{id}", name="comment_edit")
     */
    public function comment_edit(Comment $comment, Request $request, ManagerRegistry $doctrine): Response
    {
        if($comment->getUser()->getId() != $this->getUser()->getId())
        {
            $this->addFlash('error', 'You do not have permission to edit this comment!');
            return $this->redirect($this->generateUrl('user_comments'));
        }

        $form = $this->createFormBuilder($comment)
            ->add('comment', TextareaType::class)
            ->add('submit', SubmitType::class, ['label' => 'Update Comment'])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'Comment edited successfully!');
            return $this->redirectToRoute('user_comments');
        }

        return $this->render('user/comment_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comments/delete/{id}", name="comment_delete")
     */
    public function comment_delete(Comment $comment, ManagerRegistry $doctrine)
    {
        if($comment->getUser()->getId() != $this->getUser()->getId())
        {
            $this->addFlash('error', 'You do not have permission to delete this comment!');
        }

        else
        {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment deleted successfully!');
        }

        return $this->redirectToRoute('user_comments');
    }
}
