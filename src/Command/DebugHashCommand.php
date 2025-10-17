<?php

namespace App\Command;

use App\Repository\AdminUserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'debug:check-hash',
    description: 'Checks if a plain password is valid for the admin user.',
)]
class DebugHashCommand extends Command
{
    private AdminUserRepository $adminRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(AdminUserRepository $adminRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->adminRepository = $adminRepository;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('password', InputArgument::REQUIRED, 'The plain password to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $plainPassword = $input->getArgument('password');

        $io->title('Checking Admin Password Hash');

        // 1. ვპოულობთ ადმინს ბაზიდან
        $admin = $this->adminRepository->findOneBy(['email' => 'admin@example.com']);

        if (!$admin) {
            $io->error('Admin user with email "admin@example.com" not found in the database.');
            return Command::FAILURE;
        }
        $io->info('Admin user found.');

        // 2. ვიღებთ მის ჰეშირებულ პაროლს ბაზიდან
        $hashedPassword = $admin->getPassword();
        $io->writeln('Stored Hash: ' . $hashedPassword);

        // 3. ვამოწმებთ, ემთხვევა თუ არა ჩვენ მიერ გადაცემული პაროლი ბაზაში შენახულ ჰეშს
        if ($this->passwordHasher->isPasswordValid($admin, $plainPassword)) {
            $io->success('PASSWORD IS VALID!');
        } else {
            $io->error('PASSWORD IS INVALID!');
        }

        return Command::SUCCESS;
    }


}
