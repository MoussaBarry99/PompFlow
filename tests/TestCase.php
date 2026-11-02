<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        $connexion = config('database.default');
        $base = (string) config("database.connections.{$connexion}.database");

        if (! str_ends_with($base, '_test')) {
            throw new RuntimeException(
                "Tests interrompus : la base utilisée est \"{$base}\" ({$connexion}), elle doit se terminer par \"_test\"."
            );
        }

        return parent::setUpTraits();
    }
}