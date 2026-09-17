<?php

/**
 * Pure, side-effect-free sorting logic for the project list rendered on the
 * dashboard. Extracted out of index.php so it can be unit tested without a
 * real filesystem, a webserver, or any HTML output involved.
 */
final class ProjectSorter
{
    /**
     * Sorts a list of project arrays (each expected to have a 'timestamp'
     * key) so the most recently modified project comes first.
     *
     * Uses the spaceship operator instead of the original `$b - $a`
     * subtraction trick: subtraction-based comparators can silently
     * misbehave (overflow, float truncation to 0) depending on how the
     * timestamps were produced, while `<=>` is always a correct -1/0/1
     * comparison.
     *
     * @param array<int, array{name: string, timestamp: int, date: string}> $projects
     * @return array<int, array{name: string, timestamp: int, date: string}>
     */
    public static function byMostRecentlyModified(array $projects): array
    {
        usort($projects, static function (array $a, array $b): int {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return $projects;
    }
}
