<?php 
namespace App\Controller;

use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/event')]
class EventController extends AbstractController
{
    private const CAPACITY = 50;

    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userName = $data['userName'] ?? '';

        if (empty($userName)) {
            return $this->json(['error' => 'userName kötelező'], 400);
        }

        $repo = $this->em->getRepository(Registration::class);
        $existing = $repo->findOneBy(['userName' => $userName]);

        if ($existing) {
            return $this->json(['error' => 'Már jelentkeztél erre az eseményre'], 400);
        }

        $confirmedCount = $repo->countConfirmed();

        $registration = new Registration();
        $registration->setUserName($userName);

        if ($confirmedCount < self::CAPACITY) {
            $registration->setStatus('confirmed');
            $this->em->persist($registration);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'status' => 'confirmed',
                'message' => 'Sikeres jelentkezés!'
            ]);
        } else {
            $registration->setStatus('waitlist');
            $this->em->persist($registration);
            $this->em->flush();

            $position = $repo->getWaitlistPosition($userName);

            return $this->json([
                'success' => true,
                'status' => 'waitlist',
                'position' => $position,
                'message' => "Várólistán vagy a {$position}. helyen"
            ]);
        }
    }

    #[Route('/cancel', methods: ['DELETE'])]
    public function cancel(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userName = $data['userName'] ?? '';

        if (empty($userName)) {
            return $this->json(['error' => 'userName kötelező'], 400);
        }

        $registration = $this->em->getRepository(Registration::class)
            ->findOneBy(['userName' => $userName]);

        if (!$registration) {
            return $this->json(['error' => 'Nem találtunk jelentkezést'], 404);
        }

        $wasConfirmed = $registration->getStatus() === 'confirmed';
        $this->em->remove($registration);

        if ($wasConfirmed) {
            $repo = $this->em->getRepository(Registration::class);
            $firstWaitlisted = $repo->getFirstWaitlisted();

            if ($firstWaitlisted) {
                $firstWaitlisted->setStatus('confirmed');
            }
        }

        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Jelentkezés törölve'
        ]);
    }

    #[Route('/status/{userName}', methods: ['GET'])]
    public function getStatus(string $userName): JsonResponse
    {
        $registration = $this->em->getRepository(Registration::class)
            ->findOneBy(['userName' => $userName]);

        if (!$registration) {
            return $this->json(['error' => 'Nem található jelentkezés'], 404);
        }

        $response = [
            'userName' => $registration->getUserName(),
            'status' => $registration->getStatus()
        ];

        if ($registration->getStatus() === 'waitlist') {
            $repo = $this->em->getRepository(Registration::class);
            $response['position'] = $repo->getWaitlistPosition($userName);
        }

        return $this->json($response);
    }

    #[Route('/info', methods: ['GET'])]
    public function getInfo(): JsonResponse
    {
        $repo = $this->em->getRepository(Registration::class);
        $confirmedCount = $repo->countConfirmed();
        $waitlistCount = $this->em->getRepository(Registration::class)
            ->count(['status' => 'waitlist']);

        return $this->json([
            'capacity' => self::CAPACITY,
            'confirmed' => $confirmedCount,
            'available' => max(0, self::CAPACITY - $confirmedCount),
            'waitlist' => $waitlistCount
        ]);
    }
}
?>