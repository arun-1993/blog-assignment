<?php

namespace App\Service;

use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\DishRepository;
use App\Repository\UserRepository;

class Information
{
    private $userRepository, $categoryRepository, $dishRepository, $commentRepository;

    public function __construct(UserRepository $userRepository, CategoryRepository $categoryRepository, DishRepository $dishRepository, CommentRepository $commentRepository)
    {
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoryRepository;
        $this->dishRepository = $dishRepository;
        $this->commentRepository = $commentRepository;
    }

    public function overview()
    {
        $user_count = $this->userRepository->count([]);
        $category_count = $this->categoryRepository->count([]);
        $dish_count = $this->dishRepository->count([]);
        $comment_count = $this->commentRepository->count([]);

        $output = [
            "Users : $user_count",
            "Categories : $category_count",
            "Dishes : $dish_count",
            "Comments : $comment_count",
        ];

        return $output;
    }

    public function user($user)
    {
        $user_id = $this->userRepository->findOneBy(['username' => $user])->getId();

        $dish_count = $this->dishRepository->count(['author' => $user_id]);
        $comment_count = $this->commentRepository->count(['user' => $user_id]);

        $output = [
            "Name : $user",
            "Dishes : $dish_count",
            "Comments : $comment_count",
        ];

        return $output;
    }
}