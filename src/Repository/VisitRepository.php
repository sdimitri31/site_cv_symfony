<?php

namespace App\Repository;

use App\Entity\Visit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visit>
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    /**
     * @return array Contains all data in table visits
     */
    public function findVisits(): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.ip, v.url, v.method, v.visitedAt, v.visitedBy, v.userAgent')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param \DateTimeImmutable $startDate Start date to get data
     * @param \DateTimeImmutable $endDate End date to get data
     * @return array data from visits for given period
     */
    public function findVisitsByPeriod(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.ip, v.url, v.method, v.visitedAt, v.visitedBy, v.userAgent')
            ->where('v.visitedAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return int total of visits (one ip per day)
     */
    public function countTotalVisitors(): int
    {
        // Step 1: Use QueryBuilder to get visits data
        $visits = $this->createQueryBuilder('v')
            ->select('v.ip, v.visitedAt')
            ->getQuery()
            ->getArrayResult();

        // Step 2: Process data in PHP to count unique visits per day
        $uniqueVisits = [];
        foreach ($visits as $visit) {
            $ip = $visit['ip'];
            $date = $visit['visitedAt']->format('Y-m-d');

            if (!isset($uniqueVisits[$date])) {
                $uniqueVisits[$date] = [];
            }

            if (!in_array($ip, $uniqueVisits[$date])) {
                $uniqueVisits[$date][] = $ip;
            }
        }

        // Step 3: Count total unique visits
        $totalUniqueVisits = 0;
        foreach ($uniqueVisits as $visitsOnDay) {
            $totalUniqueVisits += count($visitsOnDay);
        }

        return $totalUniqueVisits;
    }

    /**
     * @param array $visits array containing visits data
     * @param \DateTimeImmutable $startDate Start date to process data
     * @param \DateTimeImmutable $endDate End date to process data
     * @return int number of visits (one ip per day)
     */
    public function countVisitors(array $visits, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): int
    {
        $uniqueViews = [];

        foreach ($visits as $visit) {
            $visitDate = $visit['visitedAt'];

            if ($visitDate >= $startDate && $visitDate <= $endDate) {
                $formattedDate = $visitDate->format('Y-m-d');
                $ip = $visit['ip'];

                // Use Ip and Date to identify a unique visit per day per ip
                $uniqueKey = $ip . '_' . $formattedDate;

                if (!isset($uniqueViews[$uniqueKey])) {
                    $uniqueViews[$uniqueKey] = true;
                }
            }
        }

        return count($uniqueViews);
    }


    /**
     * @param array $visits array containing visits data
     * @return array array containing data for charts in format ['date', 'count']
     */
    public function groupVisitorsByDay(array $visits): array
    {
        $groupedVisits = [];

        foreach ($visits as $visit) {
            $date = $visit['visitedAt']->format('Y-m-d');
            $ip = $visit['ip'];

            if (!isset($groupedVisits[$date])) {
                $groupedVisits[$date] = [];
            }

            $groupedVisits[$date][$ip] = true;
        }

        $result = [];
        foreach ($groupedVisits as $date => $ips) {
            $result[] = [
                'date' => $date,
                'count' => count($ips)
            ];
        }

        return $result;
    }

    /**
     * @return int total views
     */
    public function countTotalViews(): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array $visits array containing visits data
     * @param \DateTimeImmutable $startDate Start date to process data
     * @param \DateTimeImmutable $endDate End date to process data
     * @return int number of views
     */
    public function countViews(array $visits, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): int
    {
        $views = 0;

        foreach ($visits as $visit) {
            $visitDate = $visit['visitedAt'];

            if ($visitDate >= $startDate && $visitDate <= $endDate) {
                $views++;
            }
        }

        return $views;
    }

    /**
     * @param array $visits array containing visits data
     * @return array array containing data for charts in format ['date', 'count']
     */
    public function groupViewsByDay(array $visits): array
    {
        $groupedVisits = [];
        foreach ($visits as $visit) {
            $date = $visit['visitedAt']->format('Y-m-d');
            if (!isset($groupedVisits[$date])) {
                $groupedVisits[$date] = 0;
            }
            $groupedVisits[$date]++;
        }

        return array_map(function ($date, $count) {
            return ['date' => $date, 'count' => $count];
        }, array_keys($groupedVisits), $groupedVisits);
    }

    public function getVisitsByUrl(): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.url, COUNT(v.id) as visitCount')
            ->groupBy('v.url')
            ->orderBy('visitCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTopVisitedPages(int $limit = 10): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.url, COUNT(v.id) as visitCount')
            ->groupBy('v.url')
            ->orderBy('visitCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
