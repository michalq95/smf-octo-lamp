<?php

namespace App\EventListener;

use App\Entity\Offer;
use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Offer::class)]
class OfferEventListener
{
    public function prePersist(Offer $offer, PrePersistEventArgs $args)
    {
        // dd($offer);
        $entityManager = $args->getObjectManager();

        if ($offer->getCommaSeparatedtags()) {
            $tags = explode(',', $offer->getCommaSeparatedtags());
            $flushNeeded = false;
            $tagIds = [];
            foreach ($tags as $t) {
                $tag = $entityManager->getRepository(Tags::class)->findOneBy(['name' => $t]);
                if (!$tag) {
                    $tag = new Tags();
                    $tag->setName($t);
                    $tag->setAccepted(false);
                    $entityManager->persist($tag);
                    $flushNeeded = true;
                }
                $tagIds[] = $tag;
            }
            if ($flushNeeded) {
                $entityManager->flush();
            }
            foreach ($tagIds as $t) {
                $offer->addTag($t);
            }
        }
    }
}