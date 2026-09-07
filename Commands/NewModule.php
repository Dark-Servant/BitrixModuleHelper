<?php

namespace DarkServant\BitrixModuleHelpers\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use DarkServant\BitrixModuleHelpers\Main as MainModule;

#[AsCommand(
    name: 'module:new',
    description: 'Создание нового модуля на основе шаблона библиотеки'
)]
class NewModule extends Command
{
    protected const TEMPLATE_PATH = __DIR__ . '/../Templates/empty.module';

    protected string $name;
    protected string $title;
    protected ?string $parent;
    protected ?string $partner;

    protected string $moduleFolder;
    protected ?string $realParentName;
    protected ?string $realParentClassName;

    protected function configure(): void
    {
        $this
            ->setDescription('Создание нового модуля на основе шаблона библиотеки')
            ->addArgument('name', InputArgument::REQUIRED, 'Символьный код модуля, например vendor.module')
            ->addArgument('title', InputArgument::REQUIRED, 'Название модуля (языковая фраза *_MODULE_NAME)')
            ->addOption('parent', 'p', InputOption::VALUE_REQUIRED, 'Символьный код родительского модуля')
            ->addOption('partner', null, InputOption::VALUE_REQUIRED, 'Название партнёра (языковая фраза *_PARTNER_NAME)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->name = (string)$input->getArgument('name');
        $this->title = (string)$input->getArgument('title');
        $this->parent = $input->getOption('parent');
        $this->partner = $input->getOption('partner');
        $this->moduleFolder = $_SERVER['DOCUMENT_ROOT'] . '/local/modules';

        try
        {
            $this
                ->prepareMainModuleFolder()
                ->throwIfModuleExists()
                ->throwIfParentExists()
                ->copyTemplateFolder()
                ->correctPHPFiles()
                ->fillLangFile()
                ->fillVersionDate()
                ->setParentModule()
            ;

        } catch (\Exception $error) {
            $output->writeln('<error>' . $error->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Модуль ' . $this->name . ' создан в ' . $this->moduleFolder . '</info>');

        return Command::SUCCESS;
    }

    protected function prepareMainModuleFolder(): static
    {
        if (!is_dir($this->moduleFolder) && !mkdir($this->moduleFolder, 0775, true)) {
            new \Exception('Не удалось создать папку ' . $this->moduleFolder);
        }
        return $this;
    }  

    protected function throwIfModuleExists(): static
    {
        if ($this->findModuleByName($this->name) !== null) {
            throw new \Exception('Модуль ' . $this->name . ' уже существует');
        }
        return $this;
    }

    protected function throwIfParentExists(): static
    {
        if ($this->parent === null) {
            return $this;
        }

        if (strcasecmp($this->parent, $this->name) === 0) {
            throw new \Exception('Модуль не может быть родителем сам себе');
        }

        $this->realParentName = $this->findModuleByName($this->parent);
        if ($this->realParentName === null) {
            throw new \Exception('Указанный родительский модуль ' . $this->parent . ' не существует в ' . $this->moduleFolder);
        }

        $installFile = $this->moduleFolder . '/' . $this->realParentName . '/install/index.php';
        if (!is_file($installFile)) {
            throw new \Exception('Файл установки родительского модуля ' . $installFile . ' не найден');
        }

        require_once $installFile;
        $this->realParentClassName = strtolower(str_replace('.', '_', $this->realParentName));
        if (!is_subclass_of($this->realParentClassName, MainModule::class)) {
            throw new \Exception('Указанный родительский модуль не подходит, его класс не является дочерним к ' . MainModule::class);
        }

        return $this;
    }

    protected  function findModuleByName(string $name): ?string
    {
        foreach (scandir($this->moduleFolder) as $moduleName) {
            if (
                ($moduleName === '.') || ($moduleName === '..')
                || !is_dir($this->moduleFolder . '/' . $moduleName)
                || (strcasecmp($moduleName, $name) !== 0)
            ) {
                continue;
            }

            return $moduleName;
        }

        return null;
    }

    protected function copyTemplateFolder(): static
    {
        $targetDir = $this->moduleFolder . '/' . $this->name;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
            throw new \RuntimeException('Не удалось создать папку ' . $targetDir);
        }

        $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator(self::TEMPLATE_PATH, \FilesystemIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
        foreach ($iterator as $item) {
            $target = $targetDir . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0775, true)) {
                    throw new \RuntimeException('Не удалось создать папку ' . $target);
                }

            } elseif (!copy($item->getPathname(), $target)) {
                throw new \RuntimeException('Не удалось скопировать файл ' . $item->getPathname());
            }
        }
        return $this;
    }

    protected function correctPHPFiles(): static
    {
        $namespacePrefix = $this->getNameSpace();
        $upperPrefix = $this->getUpperPrefix();
        $className = strtolower(str_replace('.', '_', $this->name));

        foreach ($this->getPHPFiles() as $file) {
            $content = file_get_contents($file);
            $content = str_replace('Empty\Module\\', $namespacePrefix, $content);
            $content = str_replace('EMPTY_MODULE_', $upperPrefix, $content);
            $content = str_replace('empty_module', $className, $content);
            file_put_contents($file, $content);
        }
        return $this;
    }

    protected function getPHPFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($this->moduleFolder . '/' . $this->name, \FilesystemIterator::SKIP_DOTS)
                    );
        foreach ($iterator as $item) {
            if ($item->isFile() && strtolower($item->getExtension()) === 'php') {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    protected function fillLangFile(): static
    {
        $langFile = $this->moduleFolder . '/' . $this->name . '/lang/ru/install/index.php';
        if (!is_file($langFile)) {
            return $this;
        }

        $content = file_get_contents($langFile);
        $content = str_replace(
                        "'Модуль \"Пустой модуль\"'",
                        "'" . $this->escapePhpValue($this->title) . "'",
                        $content
                    );

        if ($this->partner !== null) {
            $content = str_replace(
                            $this->getUpperPrefix() . "PARTNER_NAME'] = ''",
                            $this->getUpperPrefix() . "PARTNER_NAME'] = '" . $this->escapePhpValue((string)$this->partner) . "'",
                            $content
                        );
        }

        file_put_contents($langFile, $content);
        return $this;
    }

    protected function fillVersionDate(): static
    {
        $versionFile = $this->moduleFolder . '/' . $this->name . '/install/version.php';
        if (!is_file($versionFile)) {
            return $this;
        }

        $content = file_get_contents($versionFile);
        $content = str_replace('XXXX-XX-XX', date('Y-m-d'), $content);
        file_put_contents($versionFile, $content);
        return $this;
    }

    protected function setParentModule(): static
    {
        if ($this->parent === null) {
            return $this;
        }

        $installFile = $this->moduleFolder . '/' . $this->name . '/install/index.php';
        if (!is_file($installFile)) {
            return $this;
        }

        $content = file_get_contents($installFile);
        $content = str_replace(
            'use ' . MainModule::class . ' as ModuleMain;',
            "require_once __DIR__ . '/../../" . $this->realParentName . "/install/index.php';",
            $content
        );
        $content = str_replace('extends ModuleMain', 'extends ' . $this->realParentClassName, $content);
        file_put_contents($installFile, $content);
        return $this;
    }

    protected function getUpperPrefix(): string
    {
        static $upperPrefix = null;
        if ($upperPrefix === null) {
            $upperPrefix = strtoupper(str_replace('.', '_', $this->name)) . '_';
        }
        return $upperPrefix;
    }

    protected function getNameSpace(): string
    {
        static $nameSpace = null;
        if ($nameSpace === null) {
            $nameSpace = implode('\\', array_map('ucfirst', explode('.', strtolower($this->name)))) . '\\';
        }
        return $nameSpace;
    }

    protected function escapePhpValue(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}
