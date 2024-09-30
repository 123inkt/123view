module.exports = [
    {
        ...require('eslint-config-love'),
        files: ['assets/**/*.ts'],
        rules: {
            "no-multi-spaces": ["off"],
            "no-return-assign": ["off"],
            "@typescript-eslint/no-confusing-void-expression": ["error", {"ignoreArrowShorthand": true}],
            "@typescript-eslint/no-extraneous-class": ["off"],
            "@typescript-eslint/no-invalid-void-type": ["off", {"allowAsThisParameter":  true}],
            "@typescript-eslint/no-unnecessary-boolean-literal-compare": ["off"],
            "@typescript-eslint/promise-function-async": ["off"],
            "@typescript-eslint/restrict-template-expressions": ["off"],
            "@typescript-eslint/unbound-method": ["off"],
        },
    },
]
