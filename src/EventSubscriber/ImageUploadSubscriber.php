<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final class ImageUploadSubscriber implements EventSubscriberInterface
{
    public function __construct(private ImageUploaderInterface $uploader)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.payment_method.pre_create' => 'uploadImage',
            'sylius.payment_method.pre_update' => 'uploadImage',
        ];
    }

    public function uploadImage(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        Assert::isInstanceOf($subject, ImageAwareInterface::class);

        $this->uploadSubjectImage($subject);
    }

    private function uploadSubjectImage(ImageAwareInterface $subject): void
    {
        $image = $subject->getImage();

        if (null === $image) {
            return;
        }

        if ($image->hasFile()) {
            $this->uploader->upload($image);
        }

        // Remove image if upload failed
        if (null === $image->getPath()) {
            $subject->setImage(null);
        }
    }
}
