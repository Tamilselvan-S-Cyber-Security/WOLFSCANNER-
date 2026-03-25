<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Wolf\Utils\VersionControl;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Wolf\Utils\VersionControl.
 *
 * Covered:
 * - versionString() (semantic version format: X.Y.Z)
 * - fullVersionString() (prefixed format: vX.Y.Z)
 *
 * Purpose:
 * - guard public version format from accidental changes
 * - ensure constants are composed consistently
 *
 * @todo Refactor:
 * - consider replacing constants with a Version value object
 * - consider single source of truth for version formatting
 */
final class VersionControlTest extends TestCase {
    public function testVersionString(): void {
        $expected = '0.9.12';
        $actual = VersionControl::versionString();

        $this->assertSame($expected, $actual);
    }

    public function testFullVersionString(): void {
        $expected = 'v0.9.12';
        $actual = VersionControl::fullVersionString();

        $this->assertSame($expected, $actual);
    }
}
