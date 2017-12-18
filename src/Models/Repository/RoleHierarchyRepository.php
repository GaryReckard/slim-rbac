<?php

namespace Potievdev\SlimRbac\Models\Repository;

use Doctrine\ORM\EntityRepository;
use Potievdev\SlimRbac\Helper\ArrayHelper;

/**
 * UserRoleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RoleHierarchyRepository extends EntityRepository
{
    /**
     * Returns array of child role ids for given parent role ids
     * @param integer[] $parentIds
     * @return integer[]
     */
    private function getChildIds($parentIds)
    {
        $qb = $this->createQueryBuilder('roleHierarchy');

        $qb->select('roleHierarchy.childRoleId')
            ->where($qb->expr()->in( 'roleHierarchy.parentRoleId', $parentIds))
            ->indexBy('roleHierarchy', 'roleHierarchy.childRoleId');

        $childRoleIds =  $qb->getQuery()->getArrayResult();

        return array_keys($childRoleIds);
    }

    /**
     * Finding child identifier in roles three where $parentRoleId is in the top of three
     * @param integer $parentRoleId
     * @param integer $findingChildId
     * @return bool
     */
    public function hasChildRoleId($parentRoleId, $findingChildId)
    {
        $childIds = $this->getChildIds([$parentRoleId]);

        if (count($childIds) > 0) {

            if (in_array($findingChildId, $childIds))
                return true;

            foreach ($childIds as $childId) {

                if ($this->hasChildRoleId($childId, $findingChildId) == true) {
                    return true;
                }

            }
        }

        return false;
    }

    /**
     * Returns all child role ids for given parent role ids
     * @param integer[] $parentIds
     * @return integer[]
     */
    private function getAllChildRoleIds($parentIds)
    {
        $allChildIds = [];

        while (count($parentIds) > 0) {
            $parentIds = $this->getChildIds($parentIds);
            $allChildIds = ArrayHelper::merge($allChildIds, $parentIds);
        };

        return $allChildIds;
    }

    /**
     * @param integer[] $rootRoleIds
     * @return integer[]
     */
    public function getAllRoleIdsHierarchy($rootRoleIds)
    {
        $childRoleIds = $this->getAllChildRoleIds($rootRoleIds);

        return ArrayHelper::merge($rootRoleIds, $childRoleIds);
    }
}