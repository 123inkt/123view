module.exports = [
    {
        ...require('eslint-config-love'),
        files: ['assets/**/*.ts'],
        rules: {
            "no-multi-spaces": ["off"],
            "no-return-assign": ["off"],
            "@typescript-eslint/indent": ["error", 4],
            "@typescript-eslint/no-confusing-void-expression": ["error", {"ignoreArrowShorthand": true}],
            "@typescript-eslint/no-extraneous-class": ["off"],
            "@typescript-eslint/no-invalid-void-type": ["off", {"allowAsThisParameter":  true}],
            "@typescript-eslint/no-unnecessary-boolean-literal-compare": ["off"],
            "@typescript-eslint/object-curly-spacing": ["error", "never"],
            "@typescript-eslint/promise-function-async": ["off"],
            "@typescript-eslint/restrict-template-expressions": ["off"],
            "@typescript-eslint/semi": ["error", "always"],
            "@typescript-eslint/space-before-function-paren": ["error", "never"],
            "@typescript-eslint/unbound-method": ["off"],
            "@typescript-eslint/member-delimiter-style": [
                "error",
                {
                    "multiline": {
                        "delimiter": "semi",
                        "requireLast": true
                    },
                    "singleline": {
                        "delimiter": "semi",
                        "requireLast": false
                    },
                    "multilineDetection": "brackets"
                }
            ]
        },
    },
]
