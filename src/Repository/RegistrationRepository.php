<?php 
namespace App\Repository;

use App\Entity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function countConfirmed(): int
    {
        return $this->count(['status' => 'confirmed']);
    }

    public function getWaitlistPosition(string $userName): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.status = :status')
            ->andWhere('r.createdAt < (SELECT r2.createdAt FROM App\Entity\Registration r2 WHERE r2.userName = :userName)')
            ->setParameter('status', 'waitlist')
            ->setParameter('userName', $userName);

        return $qb->getQuery()->getSingleScalarResult() + 1;
    }

    public function getFirstWaitlisted(): ?Registration
    {
        return $this->findOneBy(['status' => 'waitlist'], ['createdAt' => 'ASC']);
    }
}
?>