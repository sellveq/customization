/**
 * @category    ScandiPWA
 * @package     ScandiPWA_Customization
 * @copyright   Copyright © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

define(['jquery', 'tinycolor', 'spectrum'], function ($, tinycolor) {
    'use strict';

    var FALLBACK_COLOR = { r: 0, g: 0, b: 0, a: 1 };

    /**
     * @param {Object|null} color
     * @return {Object|null}
     */
    function fromPicker(color) {
        return color ? tinycolor(color.toRgb()) : null;
    }

    /**
     * @param {Object|null} color
     * @return {String}
     */
    function toStoredValue(color) {
        if (!color) {
            return '';
        }

        return (color.getAlpha() === 1 ? color.toHexString() : color.toHex8String()).slice(1);
    }

    /**
     * @param {String} value
     * @return {Object|null}
     */
    function parse(value) {
        var color;

        if (!value) {
            return null;
        }

        color = tinycolor(value.charAt(0) === '#' ? value : '#' + value);

        return color.isValid() ? color : null;
    }

    /**
     * @param {Object} config
     * @param {String} config.element
     * @param {String} config.pickerElement
     * @param {String} config.value
     * @param {String} [config.inheritElement]
     */
    return function (config) {
        var $field = $(config.element),
            $picker = $(config.pickerElement),
            $wrapper,
            $inherit,
            initial;

        if (!$field.length || !$picker.length) {
            return;
        }

        $wrapper = $picker.closest('.scandipwa-color-field');
        initial = parse(config.value);

        /**
         * @param {Object|null} color
         */
        function markEmpty(color) {
            $wrapper.toggleClass('_empty', !color);
        }

        /**
         * @param {Object|null} color
         */
        function pull(color) {
            var picked = fromPicker(color);

            $field.val(toStoredValue(picked));
            markEmpty(picked);
        }

        $picker.spectrum({
            color: initial ? initial.toRgb() : FALLBACK_COLOR,
            replacerClassName: 'scandipwa-color-swatch',
            preferredFormat: 'hex',
            showAlpha: true,
            showInput: false,
            showButtons: false,
            showPalette: false,
            allowEmpty: false,

            /**
             * @param {Object|null} color
             */
            move: pull,

            /**
             * @param {Object|null} color
             */
            change: pull,
        });

        $field.on('input change', function () {
            var color = parse($field.val());

            if (color) {
                $picker.spectrum('set', color.toRgb());
            }

            markEmpty(color);
        });

        if (config.inheritElement) {
            $inherit = $(config.inheritElement);

            $inherit.on('change', function () {
                $picker.spectrum(this.checked ? 'disable' : 'enable');
            });

            if ($inherit.prop('checked')) {
                $picker.spectrum('disable');
            }
        }

        markEmpty(initial);
    };
});
