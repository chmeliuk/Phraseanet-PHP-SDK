<?php

namespace PhraseanetSDK\Repository;

use PhraseanetSDK\Exception\ApiRequestException;
use PhraseanetSDK\Tools\Entity\Factory;
use PhraseanetSDK\Tools\Entity\Hydrator;
use PhraseanetSDK\Tools\Repository\RepositoryAbstract;
use Doctrine\Common\Collections\ArrayCollection;

class Feed extends RepositoryAbstract
{

    public function findById($id, $offset = 0, $perPage = 5)
    {
        $path = sprintf('/feeds/%d/content/', $id, $offset, $perPage);

        $response = $this->getClient()->call($path, array(
            'offset_start' => $offset,
            'per_page' => $perPage
        ), 'GET');

        if ($response->isOk())
        {
            $entriesCollection = new ArrayCollection();

            if ($feedDatas = $response->getResult()->feed)
            {
                $feed = Hydrator::hydrate(
                                Factory::factory('feed')
                                , $feedDatas
                );
            }

            foreach ($response->getResult()->entries->entries as $entryId => $entryDatas)
            {
                $entry = Hydrator::hydrate(
                                Factory::factory('entry')
                                , $entryDatas
                );
                
                $entry->setId($entryId);
                
                $entriesCollection->add($entry);
            }

            $feed->setEntries($entriesCollection);
            
            return $feed;
        }
        
        return null;
    }

    public function findAll()
    {
        $response = $this->getClient()->call('/feeds/list/', array(), 'GET');

        $feedCollection = new ArrayCollection();

        if ($response->isOk())
        {
            foreach ($response->getResult()->feeds as $feedDatas)
            {
                $feed = Hydrator::hydrate($this->em->getEntity('feed'), $feedDatas);

                $feedCollection->add($feed);
            }
        }
        return $feedCollection;
    }

}