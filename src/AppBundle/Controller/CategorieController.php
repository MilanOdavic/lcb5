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

class CategorieController extends Controller
{
    private function getUserId(){
      $userId = $this->get('security.token_storage')->getToken()->getUser();
      if ($userId == 'anon.')
        $userId = '';
      else
        $userId = $userId->getId();

      return $userId;
    }

    private function displayCategories($message = '')
    {
      $categories = $this->getDoctrine()
          ->getRepository('AppBundle:Categories')
          ->findAll();

      $urlIndex = $this->container->get('router')->generate('index');
      $urlArticles = $this->container->get('router')->generate('articles');
      $urlCategories = $this->container->get('router')->generate('categories');

      return $this->render('lcb/categorie.html.php', array('message' => $message, 'categories' => $categories, 'userId' => $this->getUserId(), 'urlIndex' => $urlIndex, 'urlArticles' => $urlArticles, 'urlCategories' => $urlCategories));
    }

    /**
     * @Route("/categories", name="categories")
     */
    public function categoriesAction($message='')
    {
      return $this->displayCategories();
    }

    /**
     * @Route("/createCategorie", name="createCategorie")
     * @Security("has_role('ROLE_USER')")
     */
    public function createCategorieAction()
    {
        $categorie = new Categories;
        $title = $_POST['tbTitle'];

        $categorie->setTitle($title);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUserId());
        $categorie->setUsersId($user);

        $errors = $this->get('validator')->validate($categorie);
        if (count($errors) > 0) return new Response($errors);

        $em = $this->getDoctrine()->getManager();
        $em->persist($categorie);
        $em->flush();

        return $this->displayCategories('Categorie is created.');
    }

    /**
     * @Route("/updateCategorie", name="updateCategorie")
     * @Security("has_role('ROLE_USER')")
     */
    public function updateCategorieAction()
    {
        $categorieId = $_POST['categorieId'];
        $title = $_POST['tbTitle'];

        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository('AppBundle:Categories')->find($categorieId);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($this->getUserId());

        $categorie->setTitle($title);
        $categorie->setUsersId($user);

        $errors = $this->get('validator')->validate($categorie);
        if (count($errors) > 0) return new Response($errors);

        $em->flush();

        return $this->displayCategories('Categorie is updated.');
    }

    /**
     * @Route("/deleteCategorie", name="deleteCategorie")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteCategorieAction()
    {
        $categorieId = $_POST['categorieId'];

        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository('AppBundle:Categories')->find($categorieId);

        $errors = $this->get('validator')->validate($categorie);
        if (count($errors) > 0) return new Response($errors);

        $em->remove($categorie);
        $em->flush();

        return $this->displayCategories('Categorie is deleted. With all its articles.');
    }

}
