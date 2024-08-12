<?php
declare(strict_types=1);

/*
 * This file is part of the "videos" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Videos\ViewHelpers;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AspectRatioClassViewHelper
 *
 * @phpstan-type ArgumentsArray array{
 *     aspectRatio: FileInterface|string|null,
 *     default: string|null
 * }
 */
class AspectRatioClassViewHelper extends AbstractViewHelper
{
    #[\Override]
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('aspectRatio', 'mixed', 'The desired aspect ratio, can either be a FileInterface instance or an string. If omitted, $renderChildrenClosure() will be executed to be used for inline notation.', false);
        $this->registerArgument('default', 'string', 'If no valid aspectRatio is given, this value will be used.', false);
    }

    /**
     * @param ArgumentsArray $arguments
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $defaultValue = (string)($arguments['default'] ?? '16:9');
        $aspectRatio = $arguments['aspectRatio'] ?? $renderChildrenClosure();

        // First check if the given aspectRatio argument is an file interface.
        // If so, read the "aspect_ratio" property from it
        if (
            $aspectRatio instanceof FileInterface
            && $aspectRatio->hasProperty('aspect_ratio')
        ) {
            $aspectRatio = $aspectRatio->getProperty('aspect_ratio');
        }

        // If the given aspectRatio argument is not a string or not set, use the default value
        if (! is_string($aspectRatio) || empty($aspectRatio)) {
            $aspectRatio = $defaultValue;
        }

        // Now it's time to replace the colon with a minus, so it'll be compatible with VideoJS
        $aspectRatio = str_replace(':', '-', $aspectRatio);

        // Last but not least, add the VideoJs prefix
        return 'vjs-' . $aspectRatio;
    }
}
