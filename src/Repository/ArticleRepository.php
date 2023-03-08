<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\ORM\Query;
use App\Data\SearchData;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


     /**
     * Recupére les articles en lien avec une recherche
     *
     * @return Article[]
     */
    public function findSearch(SearchData $search, int $limit = 5): array
    {
        $query = $this
            ->findActiveArticleQuery()
            ->select('a', 'u', 'c')
            ->join('a.author', 'u')
            ->join('a.category', 'c')
            ->setMaxResults($limit)
            ->setFirstResult(($search->getPage() * $limit) - $limit);

        if (!empty($search->getQ())) {
            $query = $query->andWhere('a.title LIKE :q OR a.content LIKE :q')
                ->setParameter('q', "%{$search->getQ()}%");
            // les mots a chercher exploser la chaine de string et les mettre dans un tableau
            $mots = explode(" ", $search->getQ());
            // parcourir le tableau de mots
            for ($i = 0; $i < count($mots); $i++) {
                // accepter la recherche seulement si le mot a plus de 2 lettres
                if (strlen($mots[$i]) > 2) {
                    // si le compteur est a zero ajouter WHERE a la requete
                    $query = $query->orWhere($query->expr()->orX(
                        $query->expr()->like('a.title',  ':q' . $i),
                        // $query->expr()->like('a.contenu',  ':q' . $i)
                    ))->setParameter('q' . $i, "%{$mots[$i]}%");
                }
            }
        }
        if (!empty($search->getCat())) {
            $query = $query
            ->andWhere('c.id = :id')
            ->setParameter('id', $search->getCat());
            // dd($query);
        }
        // Pagination
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // Si pas de resultats
        if (empty($data)) {
            return [];
        }
        $pages = ceil($paginator->count() / $limit);
        // Resultat final
        $result = [
            'data' => $data,
            'pages' => $pages,
            'limit' => $limit,
            'page' => $search->getPage(),
            'q' => $search->getQ(),
            'cat' => $search->getCat(),
        ];
        // dd($result);
        return $result;
    }

     /**
      * Trouver tous les Articles actives
     * @return QueryBuilder
     */
    private function findActiveArticleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = 0')
            ->orderBy('a.createdAt', 'DESC');
    }
    /**
     * Trouver tous les Articles actives
     *
     * @return Query
     */
    public function findAllActiveArticleQuery(): Query
    {
        return $this->findActiveArticleQuery()
            ->getQuery();
    }
    /**
     * Trouver les 10 dernieres articles actives
     * @return Article[]
     */
    public function findLatestActiveArticle(): array
    {
        return $this->findActiveArticleQuery()
            ->setMaxResults(10)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Trouver toutes les articles actives 
     * @return Article[]
     */
    public function findAllActiveArticle(): array
    {
        return $this->findActiveArticleQuery()
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
