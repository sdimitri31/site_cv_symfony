<?php

namespace App\Controller\Admin;

use App\Entity\Biography;
use App\Entity\Blog;
use App\Entity\Contact;
use App\Entity\Project;
use App\Entity\Setting;
use App\Entity\Visit;
use App\Repository\VisitRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly VisitRepository $visitRepository
    ) {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
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
        $visitsForPeriod = $this->visitRepository->findVisitsByPeriod($startDate, $endDate);

        // View count
        $totalViews = $this->visitRepository->countTotalViews();
        $viewsToday = $this->visitRepository->countViews($visitsForPeriod, $startOfDay, $endDate);
        $viewsWeek = $this->visitRepository->countViews($visitsForPeriod, $startOfWeek, $endDate);
        $viewsMonth = $this->visitRepository->countViews($visitsForPeriod, $startOfMonth, $endDate);

        // Views chart
        $viewsChartData = $this->visitRepository->groupViewsByDay($visitsForPeriod);

        // Visitor count
        $visitorTotal = $this->visitRepository->countTotalVisitors();
        $visitorsToday = $this->visitRepository->countVisitors($visitsForPeriod, $startOfDay, $endDate);
        $visitorsWeek = $this->visitRepository->countVisitors($visitsForPeriod, $startOfWeek, $endDate);
        $visitorsMonth = $this->visitRepository->countVisitors($visitsForPeriod, $startOfMonth, $endDate);

        // Visitor chart
        $visitorsChartData = $this->visitRepository->groupVisitorsByDay($visitsForPeriod);

        // Most visited pages
        $topPages = $this->visitRepository->findTopVisitedPages(20);

        return $this->render('admin/dashboard.html.twig', [
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

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Site Cv Symfony');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            // this defines the pagination size for all CRUD controllers
            // (each CRUD controller can override this value if needed)
            ->setPaginatorPageSize(30)
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Main site', 'fa fa-home', 'app_home');
        yield MenuItem::linkToCrud('Biography', 'fa fa-male', Biography::class);
        yield MenuItem::linkToCrud('Blog', 'fa fa-blog', Blog::class);
        yield MenuItem::linkToCrud('Project', 'fa fa-briefcase', Project::class);
        yield MenuItem::linkToCrud('Contact', 'fa fa-envelope', Contact::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Visit', 'fa fa-eye', Visit::class);
        yield MenuItem::linkToCrud('Setting', 'fa fa-wrench', Setting::class);

        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
