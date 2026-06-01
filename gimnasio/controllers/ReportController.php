<?php
require_once __DIR__ . '/../models/Security.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Membership.php';


class ReportController
{
    public static function getDashboard(): array
    {
        Security::requireAuth();
        Membership::expireOld();
        return Report::getDashboardStats();
    }

    public static function getMonthlyIncome(): array
    {
        Security::requirePermission('reports');
        $months = min((int)($_GET['months'] ?? 12), 24);
        return Report::getMonthlyIncome($months);
    }

    public static function getExpiringMemberships(): array
    {
        Security::requireAuth();
        $days = min((int)($_GET['days'] ?? 7), 30);
        return Membership::getExpiringSoon($days);
    }
}
