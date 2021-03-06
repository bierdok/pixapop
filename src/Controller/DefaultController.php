<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $fileSystem = new Filesystem();
        $finder = new Finder();

        if (!$fileSystem->exists(getenv('APP_PHOTO_DIR'))) {
            throw new BadRequestHttpException('Non-existent directory');
        }

        $galleries = [];
        /** @var File $file */
        foreach ($finder->files()->in(getenv('APP_PHOTO_DIR'))->depth(0) as $x => $file) {
            if (!$data = @exif_read_data($file->getRealPath())) {
                continue;
            }
            $time = new \DateTime(sprintf('@%d', $data['FileDateTime']));
            if (isset($data['DateTimeOriginal'])) {
                $time = new \DateTime($data['DateTimeOriginal']);
            }
            $galleries[$time->format('Y-m')][$time->getTimestamp() . $x] = [
                'name' => $file->getFilename(),
                'time' => $time->getTimestamp(),
                'size' => $file->getSize()
            ];
        }
        krsort($galleries);
        foreach ($galleries as $month => &$gallery) {
            krsort($gallery);
            $gallery = array_values($gallery);
        }

        return $this->render('index.html.twig', [
            'locale' => substr($request->getPreferredLanguage(), 0, 2),
            'pixapop' => $galleries
        ]);
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function preview(int $width, int $height, string $name)
    {
        $finder = new Finder();
        $finder->files()->in(getenv('APP_PHOTO_DIR'))->depth(0)->name($name);

        foreach ($finder as $file) {
            if (!$data = @exif_read_data($file->getRealPath())) {
                continue;
            }
            return $this->redirect(
                $this
                    ->get('liip_imagine.cache.manager')
                    ->getBrowserPath(
                        $file->getFilename(),
                        'preview',
                        ['scale' => ['dim' => [$width, $height]]]
                    )
            );
        }
        throw new NotFoundHttpException();
    }
}
