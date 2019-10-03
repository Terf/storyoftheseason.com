<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;
use \DateTime;
use \DateTimeZone;

class TeamFeedController extends AbstractController
{
    /**
     * team-feed
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        $user = null;
        $posts = [];
        if ($request->cookies->has('user_token')) {
            $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
            if ($user !== null) {
                foreach ($user->getPurchases() as $purchase) {
                    foreach ($purchase->getProduct()->getPosts() as $post) {
                        $posts[] = $post;
                    }
                }
            }
        }

        return $this->render('team_feed/index.html.twig', [
            'posts' => $posts,
            'user' => $user
        ]);
    }

    /**
     * post-submit
     */
    public function submit(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
        if ($user === null) {
            throw new \Exception("You must be signed in to post to a team feed");
        }

        $files = $request->files->get('file');
        $name = $request->request->get('name');
        $caption = $request->request->get('caption');
        $date = $request->request->get('date');
        $tz = $request->request->get('tz');
        $location = $request->request->get('location');
        $tags = $request->request->get('tags');
        $message = $request->request->get('message');

        $post = new Entity\Post;

        foreach ($files as $file) {
            if ($file->isValid()) {
                $upload = new Entity\Upload;
                $path = '/var/www/html/public/uploads/';
                $fn = $file->getClientOriginalName();
                if (file_exists($path . $fn)) {
                    $fn = uniqid() . '.' . $file->guessClientExtension();
                }
                $file->move($path, $fn);
                $upload->setFile($fn);
                $upload->setUser($user);
                $upload->setName($name);
                $upload->setCaption($caption);
                $upload->setDate(($date == null) ? null : new DateTime($date, new DateTimeZone($tz)));
                $upload->setLocation($location);
                $upload->setTags($tags);
                $upload->setMessage($message);
                if (explode('/', $upload->getMimeType())[0] === 'video' && $file->guessClientExtension() !== 'mp4') {
                    $process = new Process(['ffmpeg', "-i {$path}{$upload->getFile()}", '-codec', 'copy', "{$path}{$upload->getFile()}.mp4"]);
                    $process->start();
                    $upload->setFile("{$upload->getFile()}.mp4");
                }
                $post->addUpload($upload);
                $entityManager->persist($upload);
            }
        }

        $post->setProduct($entityManager->getRepository(Entity\Product::class)->find($request->request->get('product')));
        $post->setText($request->request->get('text'));
        $post->setDate(new DateTime('now', new DateTimeZone($tz)));
        $entityManager->persist($post);
        $entityManager->flush();
        return $this->redirectToRoute('team-feed');
    }

    /**
     * comment-submit
     */
    public function comment(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
        if ($user === null) {
            throw new \Exception("You must be signed in to post a comment");
        }

        $text = $request->request->get('comment');
        $post = $entityManager->getRepository(Entity\Post::class)->find($request->request->get('post'));

        $comment = new Entity\Comment;
        $comment->setUser($user);
        $comment->setPost($post);
        $comment->setComment($text);

        $entityManager->persist($comment);
        $entityManager->flush();
        return $this->redirectToRoute('team-feed');
    }

    /**
     * like-submit
     */
    public function like(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
        if ($user === null) {
            throw new \Exception("You must be signed in to like");
        }

        $post = $entityManager->getRepository(Entity\Post::class)->find($request->request->get('post'));
        $post->addLike($user);

        $entityManager->merge($post);
        $entityManager->flush();
        return $this->redirectToRoute('team-feed');
    }

    /**
     * remove-like-submit
     */
    public function removeLike(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
        if ($user === null) {
            throw new \Exception("You must be signed in to remove a like");
        }

        $post = $entityManager->getRepository(Entity\Post::class)->find($request->request->get('post'));
        $post->removeLike($user);
        
        $entityManager->merge($post);
        $entityManager->flush();
        return $this->redirectToRoute('team-feed');
    }

    /**
     * post-all
     */
    public function allPosts(Request $request, EntityManagerInterface $entityManager)
    {
        $user = null;
        $posts = [];
        if ($request->cookies->has('user_token')) {
            $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
            if ($user !== null) {
                foreach ($user->getPurchases() as $purchase) {
                    foreach ($purchase->getProduct()->getPosts() as $post) {
                        $posts[] = $post;
                    }
                }
            }
        }

        return $this->render('team_feed/posts.html.twig', [
            'posts' => $posts,
            'user' => $user
        ]);
    }

    /**
     * post
     */
    public function post(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $user = null;
        if ($request->cookies->has('user_token')) {
            $user = $entityManager->getRepository(Entity\Buyer::class)->findOneBy(['token' => $request->cookies->get('user_token')]);
        }

        return $this->render('team_feed/post.html.twig', [
            'user' => $user,
            'post' => $entityManager->getRepository(Entity\Post::class)->find($id)
        ]);
    }

}
