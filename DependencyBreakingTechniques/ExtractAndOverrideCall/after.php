<?php

declare(strict_types=1);

namespace App
{
    interface StyleTemplate
    {
        public function setCount(int $count): void;
    }

    class DefaultStyleTemplate implements StyleTemplate
    {
        public function setCount(int $count): void
        {
            // ...
        }
    }

    class Style
    {
        public function __construct(private string $name) {}

        public function getName(): string
        {
            return $this->name;
        }
    }

    class StyleManager 
    {
        /** @return Style[] */
        public static function formStyles(StyleTemplate $styleTemplate, int $id): array
        {
            // ...
            // ...

            // The return values can change at runtime
            // This is just returning an array of Styles as an example 
            return [
                new Style("This"),
                new Style("That")
            ];
        }
    }

    class PageLayout
    {
        public function __construct(
            private int $id,
            private array $styles,
            private StyleTemplate $styleTemplate
        ) {}

        //...

        /**
         * This is the method we want to add behaviour to and get under test
         */
        public function rebindStyles(): void
        {
            // ...

            $this->styles = $this->getStylesFromStyleManager();

            // ...

            // Our new piece of code
            if ($this->styles[0]->getName() === 'Other') {
                $this->styleTemplate->setCount(count($this->styles) - 1);
            }
        }

        // This is a "scar" from refactoring legacy code
        protected function getStylesFromStyleManager(): array
        {
            return StyleManager::formStyles($this->styleTemplate, $this->id);
        }

        public function getStyles(): array
        {
            return $this->styles;
        }
    }
}

namespace Test
{
    use App;
    use PHPUnit\Framework\TestCase;

    class MockTemplate implements App\StyleTemplate
    {
        public int $count = 0;
        public function setCount(int $count): void
        {
            $this->count = $count;
        }
    }

    class TestPageLayout extends App\PageLayout
    {
        public array $managerStyles = [];

        protected function getStylesFromStyleManager(): array
        {
            return $this->managerStyles;
        }
    }

    class PageLayoutTest extends TestCase
    {
        /** @test */
        public function rebindStylesExpectsStyleTemplateToRecieveCountForOtherStyle(): void
        {
            $mockTemplate = new MockTemplate();

            $styles = [
                new App\Style('Other'),
                new App\Style(''),
                new App\Style('')
            ];

            $pageLayout = new TestPageLayout(
                1,
                [],
                $mockTemplate
            );
            $pageLayout->managerStyles = $styles;

            // Method under test
            $pageLayout->rebindStyles();

            $this->assertEquals(2, $mockTemplate->count);
        }
    }
}
