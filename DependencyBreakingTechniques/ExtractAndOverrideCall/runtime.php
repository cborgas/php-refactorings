<?php

declare(strict_types=1);

/**
 * Here is some code that is executed at runtime
 * 
 * Be careful not to break it...
 */
namespace Runtime
{
    use App;
    use PHPUnit\Framework\TestCase;

    class PageLayoutTest extends TestCase
    {
        /** @test */
        public function regression(): void
        {
            $pageLayout = new App\PageLayout(
                1,
                [
                    new App\Style('first style'),
                    new App\Style('second style')
                ],
                new App\DefaultStyleTemplate()
            );
        
            $pageLayout->rebindStyles();
            
            $this->assertEquals('This', $pageLayout->getStyles()[0]->getName());
            $this->assertEquals('That', $pageLayout->getStyles()[1]->getName());
        }
    }
}
