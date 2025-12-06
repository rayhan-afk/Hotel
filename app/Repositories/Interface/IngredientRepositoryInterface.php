<?php

namespace App\Repositories\Interface;

interface IngredientRepositoryInterface
{
    public function getIngredientsDatatable($request);
    public function getCategories();
}