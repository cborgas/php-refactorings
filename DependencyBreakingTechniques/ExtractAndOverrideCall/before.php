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
            echo $count . PHP_EOL;
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

            $this->styles = StyleManager::formStyles($this->styleTemplate, $this->id);

            // ...
        }

        public function getStyles(): array
        {
            return $this->styles;
        }
    }
}
