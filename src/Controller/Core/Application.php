<?php
namespace App\Controller\Core;


use App\Controller\Utils\Utils;
use App\Entity\User;
use App\Services\Core\Logger;
use App\Services\Core\Translator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class Application extends AbstractController {

    /**
     * @var Repositories
     */
    public $repositories;

    /**
     * @var Forms
     */
    public $forms;

    /**
     * @var EntityManagerInterface
     */
    public $em;

    /**
     * @var \App\Services\Core\Translator $translator
     */
    public $translator;

    /**
     * @var LoggerInterface $logger
     */
    public $logger;

    /**
     * @var Logger $custom_loggers
     */
    public $custom_loggers;

    /**
     * @var Settings
     */
    public $settings;

    /**
     */
    public $translations;

    /**
     * @var ConfigLoaders $config_loaders
     */
    public $config_loaders;

    /**
     * @var TokenStorageInterface $token_storage
     */
    private TokenStorageInterface $token_storage;

    public function __construct(
        Repositories            $repositories,
        Forms                   $forms,
        EntityManagerInterface  $em,
        LoggerInterface         $logger,
        Settings                $settings,
        Logger                  $custom_loggers,
        TranslatorInterface     $translator,
        ConfigLoaders           $config_loaders,
        TokenStorageInterface   $token_storage
    ) {
        $this->custom_loggers   = $custom_loggers;
        $this->repositories     = $repositories;
        $this->settings         = $settings;
        $this->logger           = $logger;
        $this->forms            = $forms;
        $this->em               = $em;
        $this->translator       = new Translator($translator);
        $this->config_loaders   = $config_loaders;
        $this->token_storage    = $token_storage;
    }

    /**
     * Adds green box message on front
     * @param $message
     */
    public function addSuccessFlash($message)
    {
        $this->addFlash(Utils::FLASH_TYPE_SUCCESS, $message);
    }

    /**
     * Adds red box message on front
     * @param $message
     */
    public function addDangerFlash($message)
    {
        $this->addFlash(Utils::FLASH_TYPE_DANGER, $message);
    }

    /**
     * @param string $camel_string
     * @return string
     */
    public static function camelCaseToSnakeCaseConverter(string $camel_string)
    {
        $camel_case_to_snake_converter = new CamelCaseToSnakeCaseNameConverter(null, true);
        $snake_string                  = $camel_case_to_snake_converter->normalize($camel_string);
        return $snake_string;
    }

    /**
     * @param string $snake_case
     * @return string
     */
    public static function snakeCaseToCamelCaseConverter(string $snake_case)
    {
        $camel_case_to_snake_converter = new CamelCaseToSnakeCaseNameConverter(null, true);
        $camel_string                  = $camel_case_to_snake_converter->denormalize($snake_case);
        return $camel_string;
    }

    /**
     * Logs the standard exception data
     * @param Throwable $throwable
     */
    public function logExceptionWasThrown(Throwable $throwable): void
    {
        $message = $this->translator->translate('messages.general.internalServerError');

        $this->logger->critical($message, [
            "exceptionMessage" => $throwable->getMessage(),
            "exceptionCode"    => $throwable->getCode(),
            "exceptionTrace"   => $throwable->getTraceAsString(),
        ]);
    }

    /**
     * Returns currently logged in user
     * @return object|UserInterface|null
     */
    public function getCurrentlyLoggedInUser()
    {
        return $this->getUser();
    }

    /**
     * Will force logout from system
     */
    public function logoutCurrentlyLoggedInUser()
    {
        $this->token_storage->setToken(null);
    }
}