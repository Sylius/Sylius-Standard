<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Repository;

use AppBundle\Repository\ProductRepository as BaseProductRepository;
use Sylius\Component\Core\Model\ChannelInterface;




/**
 * Description of ProductRepositoryExt
 * Date 08.10.2017
 * @author akorchagin
 */
class ProductRepositoryExt extends BaseProductRepository{
    /**
     * {@inheritdoc}
     */
    public function findAllByChannelAndTaxonId(ChannelInterface $channel, $locale, $taxonId){
        $products = $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->innerJoin('o.productTaxons', 'productTaxon')
            ->andWhere('productTaxon.taxon = :taxonId')
            ->andWhere(':channel MEMBER OF o.channels')
            ->andWhere('o.enabled = true')
            ->orderBy("UPPER(translation.name)", 'ASC')
            ->setParameter('channel', $channel)
            ->setParameter('locale', $locale)
            ->setParameter('taxonId', $taxonId)
            ->getQuery()
            ->getResult()
        ;

        return $products;
    }
}
