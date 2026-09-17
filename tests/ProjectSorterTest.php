<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/ProjectSorter.php';

final class ProjectSorterTest extends TestCase
{
    public function testSortsProjectsByMostRecentlyModifiedFirst(): void
    {
        $projects = [
            ['name' => 'oldest', 'timestamp' => 100],
            ['name' => 'newest', 'timestamp' => 300],
            ['name' => 'middle', 'timestamp' => 200],
        ];

        $sorted = ProjectSorter::byMostRecentlyModified($projects);

        $this->assertSame(
            ['newest', 'middle', 'oldest'],
            array_column($sorted, 'name')
        );
    }

    public function testReturnsEmptyArrayUnchanged(): void
    {
        $this->assertSame([], ProjectSorter::byMostRecentlyModified([]));
    }

    public function testSingleProjectIsReturnedAsIs(): void
    {
        $projects = [['name' => 'solo', 'timestamp' => 123]];

        $this->assertSame($projects, ProjectSorter::byMostRecentlyModified($projects));
    }

    public function testProjectsWithEqualTimestampsAreBothKept(): void
    {
        $projects = [
            ['name' => 'a', 'timestamp' => 500],
            ['name' => 'b', 'timestamp' => 500],
        ];

        $sorted = ProjectSorter::byMostRecentlyModified($projects);

        // Both entries must survive the sort (no data loss), regardless of
        // their relative order when timestamps tie.
        $this->assertCount(2, $sorted);
        $this->assertEqualsCanonicalizing(['a', 'b'], array_column($sorted, 'name'));
    }

    public function testDoesNotMutateTheInputArray(): void
    {
        $projects = [
            ['name' => 'first', 'timestamp' => 1],
            ['name' => 'second', 'timestamp' => 2],
        ];
        $original = $projects;

        ProjectSorter::byMostRecentlyModified($projects);

        $this->assertSame($original, $projects);
    }

    public function testHandlesNegativeAndZeroTimestamps(): void
    {
        // filemtime()-derived values are always non-negative in practice,
        // but the comparator itself should not assume that.
        $projects = [
            ['name' => 'zero', 'timestamp' => 0],
            ['name' => 'negative', 'timestamp' => -10],
            ['name' => 'positive', 'timestamp' => 10],
        ];

        $sorted = ProjectSorter::byMostRecentlyModified($projects);

        $this->assertSame(
            ['positive', 'zero', 'negative'],
            array_column($sorted, 'name')
        );
    }
}
