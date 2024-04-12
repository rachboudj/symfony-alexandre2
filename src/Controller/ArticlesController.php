<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticleType;
use App\Repository\ArticlesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;


class ArticlesController extends AbstractController
{
    #[Route('/article', name: 'article')]
    public function index(ArticlesRepository $articlesRepository): Response
    {
        $articles = $articlesRepository->findAll();

        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Articles();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('articles/new.html.twig', [
            'articleForm' => $form->createView(),
            'article' => $article
        ]);
    }

    #[Route('/article/edit/{id}', name: 'article_edit')]
    #[IsGranted(new Expression(
        '"ROLE_ADMIN" in role_names or "ROLE_MODO" in role_names',
    ))]
    public function edit(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('articles/edit.html.twig', [
            'articleForm' => $form->createView(),
            'article' => $article
        ]);
    }

    #[Route('/article/delete/{id}', name: 'article_delete')]
    public function delete(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {

            $entityManager->remove($article);
            $entityManager->flush();

        return $this->redirectToRoute('article');
    }


    #[Route('/article/get/{id}', name: 'article_show')]
    public function show(int $id, ArticlesRepository $articlesRepository, CacheInterface $cache): Response
    {
        $cacheKey = 'article_' . $id;

    // Récupérer ou générer le contenu de l'article
    $response = $cache->get($cacheKey, function (ItemInterface $item) use ($id, $articlesRepository) {
        $item->expiresAfter(3600); // Le cache expire après une heure

        $article = $articlesRepository->find($id);
        if (!$article) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas');
        }

        // Préparer le contenu de la vue avec from_cache à false
        return $this->render('articles/show.html.twig', [
            'article' => $article,
            'from_cache' => false  // Indicateur de cache pour la vue
        ])->getContent(); // Obtenir le contenu HTML de la réponse
    });

    // Vérifier si le contenu vient du cache ou pas
    if (strpos($response, 'from_cache: false') !== false) {
        $response = str_replace('from_cache: false', 'from_cache: true', $response);
    }

    // Retourner une nouvelle réponse avec le contenu modifié
    return new Response($response);
    }
}
