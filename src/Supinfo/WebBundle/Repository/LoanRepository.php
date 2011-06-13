<?php

namespace Supinfo\WebBundle\Repository;

/**
 * LoanRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LoanRepository extends EntityRepository
{

    public function get5NextLoansQB()
    {
        $qb = $this->selectQB();

        $qb->orderBy(
            $qb->getRootAlias().'.dateStart',
            'ASC'
        )->andWhere(
            $qb->expr()->gt(
                $qb->getRootAlias().'.dateStart',
                'CURRENT_DATE()'
            )
        )->setMaxResults(
            5
        );

        return $qb;
    }

    public function get5NextLoans()
    {
        return $this->get5NextLoansQB()->getQuery()->getResult();
    }

    public function getCurrentLoansQB()
    {
        $qb = $this->selectQB();

        $qb->andWhere(
            $qb->expr()->lt(
                $qb->getRootAlias().'.dateStart',
                'CURRENT_DATE()'
            )
        )->andWhere(
            $qb->expr()->gt(
                $qb->getRootAlias().'.dateEnd',
                'CURRENT_DATE()'
            )
        );

        return $qb;
    }

    public function getCurrentLoans()
    {
        return $this->getCurrentLoansQB()->getQuery()->getResult();
    }

}