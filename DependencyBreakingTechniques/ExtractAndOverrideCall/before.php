<?php

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

/**
 * Here is some code that is executed at runtime
 * 
 * Be careful not to break it...
 */
namespace Runtime
{
    use App;

    $pageLayout = new App\PageLayout(
        1,
        [
            new App\Style('first style'),
            new App\Style('second style')
        ],
        new App\DefaultStyleTemplate()
    );

    $pageLayout->rebindStyles();

    assert("This" === $pageLayout->getStyles()[0]->getName());
    assert("That" === $pageLayout->getStyles()[1]->getName());
}
