<?php

namespace App\EventListener;

use App\Entity\Indicator\AbstractIndicator;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Entity as MappingEntity;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Écouteur Doctrine.
 */
class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $em = $args->getObjectManager();

        $entityClass = get_class($entity);

        switch ($entityClass) {
            case 'App\Entity\Commune':
                // Communes les plus proches
                $entity->setNearestCommunes(
                    $em->getRepository($entityClass)->findNearestFromCommune($entity)
                );
                break;
            case 'App\Entity\Quartier':
                // Quartiers les plus proches
                $entity->setNearestQuartiers(
                    $em->getRepository($entityClass)->findNearestFromQuartier($entity)
                );
                break;
            case 'App\Entity\Region':
                // Régions les plus proches
                $entity->setNearestRegions(
                    $em->getRepository($entityClass)->findNearestFromRegion($entity)
                );
                break;
            case 'App\Entity\Indicator\IntIndicator':
            case 'App\Entity\Indicator\RatioIndicator':
            case 'App\Entity\Indicator\StringIndicator':
            case 'App\Entity\Indicator\TextIndicator':
                $kelQuartierId = $entity->getKelQuartierId();
                if (isset(AbstractIndicator::LABELS[$kelQuartierId])) {
                    $label = AbstractIndicator::LABELS[$kelQuartierId];
                    $entity->setLabel($label);
                }
                break;
        }
    }
}
