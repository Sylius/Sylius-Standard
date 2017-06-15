<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Repository;

/**
 * Description of StaticContentRepository
 * Date 15.06.2017
 * @author akorchagin
 */

use Doctrine\ORM\EntityRepository as EntityRepository;
use AppBundle\Repository\StaticContentInterface;

class StaticContentRepository extends EntityRepository implements StaticContentInterface {
    /**
     * {@inheritdoc}
     **/
    public function findOneBySlug($slug)
    {
        $static_content = $this->createQueryBuilder('o')
            ->andWhere('o.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        return $static_content;
    }    
}
