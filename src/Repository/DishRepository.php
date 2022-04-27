<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Dish;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dish|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dish|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dish[]    findAll()
 * @method Dish[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DishRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dish::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Dish $dish, User $author, $image, bool $flush = true): void
    {
        $dish->setAuthor($author);
        $dish->setImage($image);

        $this->_em->persist($dish);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function edit(Dish $dish, $input, bool $flush = true): void
    {
        if(isset($input['image']))
        {
            $dish->setImage($input['image']);
        }
        
        $dish->setName($input['name']);
        $dish->setCategory($input['category']);
        $dish->setDescription($input['description']);
        $dish->setContent($input['content']);
        $dish->setAuthor($input['author']);

        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Dish $dish, bool $flush = true): void
    {
        $commentRepository = $this->_em->getRepository(Comment::class);
        $commentRepository->removeAll($commentRepository->findBy(['dish' => $dish->getId()]));

        $this->_em->remove($dish);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Dish[]
     */
    public function getRecentDishes(): array
    {
        $query = $this->_em->createQuery(
            'SELECT dish FROM App\Entity\Dish dish ORDER BY dish.createdOn DESC'
        );

        return $query->setMaxResults(3)->getResult();
    }

    /**
     * @return Dish[]
     */
    public function getByDate($date): array
    {
        if(isset($date))
        {
            $query = $this->_em
                ->createQuery(
                    'SELECT dish FROM App\Entity\Dish dish WHERE dish.createdOn >= :date_start AND dish.createdOn <= :end_date ORDER BY dish.name ASC'
                )
                ->setParameter('date_start', $date->format('Y-m-d 00:00:00'))
                ->setParameter('end_date', $date->format('Y-m-d 23:59:59'))
            ;
        }

        else
        {
            $query = $this->_em
                ->createQuery(
                    'SELECT dish FROM App\Entity\Dish dish ORDER BY dish.name ASC'
                )
            ;
        }

        return $query->getResult();
    }

    /**
     * @return Dish[]
     */
    public function distinctCategory($author): array
    {
        $query = $this->_em
            ->createQuery(
                'SELECT DISTINCT(dish.category) FROM App\Entity\Dish dish WHERE dish.author = :author'
            )
            ->setParameter('author', $author)
        ;

        return $query->getResult();
    }

    // /**
    //  * @return Dish[] Returns an array of Dish objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Dish
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
