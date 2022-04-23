<?php

namespace App\Service;

use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\DishRepository;
use App\Repository\UserRepository;

class Information
{
    public function overview(UserRepository $userRepository, CategoryRepository $categoryRepository, DishRepository $dishRepository, CommentRepository $commentRepository)
    {
        $user_count = $userRepository->count([]);
        $category_count = $categoryRepository->count([]);
        $dish_count = $dishRepository->count([]);
        $comment_count = $commentRepository->count([]);

        return array($user_count, $category_count, $dish_count, $comment_count);
    }
}