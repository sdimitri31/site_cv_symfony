<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/blog')]
class BlogController extends AbstractController
{
    private PaginatorInterface $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    #[Route('/', name: 'app_blog_index', methods: ['GET'])]
    public function index(Request $request, BlogRepository $blogRepository): Response
    {
        $queryBuilder = $blogRepository->createQueryBuilder('b')
            ->andWhere('b.isVisible = :visible')
            ->setParameter('visible', true)
            ->orderBy('b.updatedAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder, // query or iterable
            $request->query->getInt('page', 1), // page number
            9 // limit per page
        );

        return $this->render('blog/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    #[Route('/{slug}', name: 'app_blog_show_slug', requirements: ['slug' => '[a-zA-Z0-9\-]+'], methods: ['GET'])]
    public function showBySlug(BlogRepository $blogRepository, string $slug): Response
    {
        $blog = $blogRepository->findOneBy(['slug' => $slug]);

        if (!$blog) {
            throw $this->createNotFoundException('Article de blog non trouvé');
        }

        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    #[Route('/tag/{tag}', name: 'app_blog_search_tag', methods: ['GET'])]
    public function searchByTag(Request $request, BlogRepository $blogRepository, string $tag): Response
    {
        $queryBuilder = $blogRepository->getQueryBuilderFindByTag($tag);

        $pagination = $this->paginator->paginate(
            $queryBuilder, // query or iterable
            $request->query->getInt('page', 1), // page number
            9 // limit per page
        );

        return $this->render('blog/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }
}
