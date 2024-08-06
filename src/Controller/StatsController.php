<?php

namespace App\Controller;

use App\Repository\VisitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'stats')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(VisitRepository $visitRepository): Response
    {
        // Separate variables for different periods
        $today = new \DateTimeImmutable('today');
        $startOfDay = $today->setTime(0, 0);
        $endOfDay = $today->setTime(23, 59, 59);
        $startOfWeek = $today->modify('-7 days')->setTime(0, 0);
        $startOfMonth = $today->modify('-30 days')->setTime(0, 0);

        // Data array for a selected period
        $startDate = $startOfMonth;
        $endDate = $endOfDay;
        $visitsForPeriod = $visitRepository->findVisitsByPeriod($startDate, $endDate);

        // View count
        $totalViews = $visitRepository->countTotalViews();
        $viewsToday = $visitRepository->countViews($visitsForPeriod, $startOfDay, $endDate);
        $viewsWeek = $visitRepository->countViews($visitsForPeriod, $startOfWeek, $endDate);
        $viewsMonth = $visitRepository->countViews($visitsForPeriod, $startOfMonth, $endDate);

        // Views chart
        $viewsChartData = $visitRepository->groupViewsByDay($visitsForPeriod);

        // Visitor count
        $visitorTotal = $visitRepository->countTotalVisitors();
        $visitorsToday = $visitRepository->countVisitors($visitsForPeriod, $startOfDay, $endDate);
        $visitorsWeek = $visitRepository->countVisitors($visitsForPeriod, $startOfWeek, $endDate);
        $visitorsMonth = $visitRepository->countVisitors($visitsForPeriod, $startOfMonth, $endDate);

        // Visitor chart
        $visitorsChartData = $visitRepository->groupVisitorsByDay($visitsForPeriod);

        // Most visited pages
        $topPages = $visitRepository->findTopVisitedPages(20);

        return $this->render('stats/index.html.twig', [
            'pageViewedTotal' => $totalViews,
            'pageViewedToday' => $viewsToday,
            'pageViewedWeek' => $viewsWeek,
            'pageViewedMonth' => $viewsMonth,
            'visitorTotal' => $visitorTotal,
            'visitorsToday' => $visitorsToday,
            'visitorsWeek' => $visitorsWeek,
            'visitorsMonth' => $visitorsMonth,
            'visitorsChartData' => $visitorsChartData,
            'viewsChartData' => $viewsChartData,
            'topPages' => $topPages
        ]);
    }
}
