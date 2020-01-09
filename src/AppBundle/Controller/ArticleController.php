<?php

namespace AppBundle\Controller;
use AppBundle\Entity\Articles;
use AppBundle\Entity\User;
use AppBundle\Entity\Categories;
use AppBundle\Entity\Comments;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    private function getUserId(){
      $userId = $this->get('security.token_storage')->getToken()->getUser();
      if ($userId == 'anon.')
        $userId = '';
      else
        $userId = $userId->getId();

      return $userId;
    }

    private function displayArticles($message = '')
    {
      $articles = $this->getDoctrine()
          ->getRepository('AppBundle:Articles')
          ->findAll();

      $comments = $this->getDoctrine()
          ->getRepository('AppBundle:Comments')
          ->findAll();

      $urlIndex = $this->container->get('router')->generate('index');
      $urlArticles = $this->container->get('router')->generate('articles');
      $urlCategories = $this->container->get('router')->generate('categories');

      return $this->render('lcb/article.html.php', array('message' => $message, 'articles' => $articles, 'comments' => $comments, 'userId' => $this->getUserId(), 'urlIndex' => $urlIndex, 'urlArticles' => $urlArticles, 'urlCategories' => $urlCategories));
    }

    /**
     * @Route("/articles", name="articles")
     */
    public function articlesAction()
    {
        return $this->displayArticles();
    }

    /**
     * @Route("/createArticle", name="createArticle")
     * @Security("has_role('ROLE_USER')")
     */
    public function createArticleAction()
    {
        $article = new Articles;
        $categoriesId = $_POST['tbCategoriesId'];
        $text = $_POST['tbText'];
        $title = $_POST['tbTitle'];

        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository('AppBundle:Categories')->find($categoriesId);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUserId());

        $article->setCategoriesId($categorie);
        $article->setText($text);
        $article->setTitle($title);
        $article->setUsersId($user);

        $errors = $this->get('validator')->validate($article);
        if (count($errors) > 0) return new Response($errors);

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $this->displayArticles("Article is created.");
    }

    // 5
    /**
     * @Route("/updateArticle", name="updateArticle")
     * @Security("has_role('ROLE_USER')")
     */
    public function updateArticleAction()
    {
        $idArticles = $_POST['articleId'];
        $categoriesId = $_POST['tbCategoriesId'];
        $text = $_POST['tbText'];
        $title = $_POST['tbTitle'];

        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Articles')->find($idArticles);
        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository('AppBundle:Categories')->find($categoriesId);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUserId());

        $article->setCategoriesId($categorie);
        $article->setText($text);
        $article->setTitle($title);
        $article->setUsersId($user);

        $errors = $this->get('validator')->validate($article);
        if (count($errors) > 0) return new Response($errors);

        $em->flush();

        return $this->displayArticles("Article is updated.");
    }

    /**
     * @Route("/createComment", name="createComment")
     * @Security("has_role('ROLE_USER')")
     */
    public function createCommentAction()
    {
        $comment = new Comments;
        $title = $_POST['tbTitle'];
        $text = $_POST['tbText'];
        $articleId = $_POST['articleId'];

        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Articles')->find($articleId);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUserId());

        $comment->setTitle($title);
        $comment->setText($text);
        $comment->setArticlesId($article);
        $comment->setUsersId($user);

        $errors = $this->get('validator')->validate($comment);
        if (count($errors) > 0) return new Response($errors);

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        return $this->displayArticles("Comment is created.");
    }

    /**
     * @Route("/updateComment", name="updateComment")
     * @Security("has_role('ROLE_USER')")
     */
    public function updateCommentAction()
    {
        $idComment = $_POST['commentId'];
        $title = $_POST['tbTitle'];
        $text = $_POST['tbText'];
        $articleId = $_POST['articleId'];

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comments')->find($idComment);
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Articles')->find($articleId);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUserId());

        $comment->setTitle($title);
        $comment->setText($text);
        $comment->setArticlesId($article);
        $comment->setUsersId($user);

        $errors = $this->get('validator')->validate($comment);
        if (count($errors) > 0) return new Response($errors);

        $em->flush();

        return $this->displayArticles("Comment is updated.");
    }

    /**
     * @Route("/deleteArticle", name="deleteArticle")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteArticleAction()
    {
        $articleId = $_POST['articleId'];

        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Articles')->find($articleId);

        $errors = $this->get('validator')->validate($article);
        if (count($errors) > 0) return new Response($errors);

        $em->remove($article);
        $em->flush();

        return $this->displayArticles("Article is deleted.");
    }

    /**
     * @Route("/deleteComment", name="deleteComment")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteCommentAction()
    {
        $commentId = $_POST['commentId'];

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comments')->find($commentId);

        $errors = $this->get('validator')->validate($comment);
        if (count($errors) > 0) return new Response($errors);

        $em->remove($comment);
        $em->flush();

        return $this->displayArticles("Comment is deleted.");
    }

}
