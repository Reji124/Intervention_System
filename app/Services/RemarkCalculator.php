<?php

namespace App\Services;

/**
 * RemarkCalculator
 * 
 * Calculates performance remarks based on pass rate percentage.
 * Used across all analytics reports for consistent grading.
 */
class RemarkCalculator
{
    /**
     * Remark levels and their pass rate ranges.
     */
    private const REMARK_LEVELS = [
        ['min' => 91, 'max' => 100, 'label' => 'Excellent', 'class' => 'excellent'],
        ['min' => 81, 'max' => 90, 'label' => 'Very Good', 'class' => 'very-good'],
        ['min' => 61, 'max' => 80, 'label' => 'Good', 'class' => 'good'],
        ['min' => 41, 'max' => 60, 'label' => 'Fair', 'class' => 'fair'],
        ['min' => 21, 'max' => 40, 'label' => 'Poor', 'class' => 'poor'],
        ['min' => 0, 'max' => 20, 'label' => 'Very Poor', 'class' => 'very-poor'],
    ];

    /**
     * Get remark label for a given pass rate percentage.
     * 
     * @param int $passRate Pass rate percentage (0-100)
     * @return string Remark label
     */
    public function getRemarkLabel(int $passRate): string
    {
        return $this->getRemarkData($passRate)['label'];
    }

    /**
     * Get CSS badge class for a given pass rate percentage.
     * 
     * @param int $passRate Pass rate percentage (0-100)
     * @return string CSS class name for badge styling
     */
    public function getBadgeClass(int $passRate): string
    {
        return $this->getRemarkData($passRate)['class'];
    }

    /**
     * Get full remark data for a given pass rate percentage.
     * 
     * @param int $passRate Pass rate percentage (0-100)
     * @return array ['label' => string, 'class' => string, 'min' => int, 'max' => int]
     */
    public function getRemarkData(int $passRate): array
    {
        $passRate = max(0, min(100, $passRate)); // Clamp to 0-100

        foreach (self::REMARK_LEVELS as $level) {
            if ($passRate >= $level['min'] && $passRate <= $level['max']) {
                return $level;
            }
        }

        // Fallback (shouldn't reach here)
        return self::REMARK_LEVELS[5];
    }

    /**
     * Get all remark levels (for reference).
     */
    public static function getAllLevels(): array
    {
        return self::REMARK_LEVELS;
    }
}
