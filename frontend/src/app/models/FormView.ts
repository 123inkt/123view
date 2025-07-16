/* eslint-disable @typescript-eslint/no-empty-object-type */
type FormView<T extends {[key: string]: FormView} = {}> = {
    vars: {
        action: string;
        attr: Record<string, boolean | number | string>;
        block_prefixes: string[];
        disabled: boolean;
        full_name: string;
        help_attr: Record<string, boolean | number | string>;
        help_html: boolean;
        id: string;
        label: string;
        label_attr: Record<string, boolean | number | string>;
        label_html: boolean;
        method: string;
        name: string;
        required: boolean;
        unique_block_prefix: string;
        value: string;
    };
} & {
    [K in keyof T]: T[K];
};

export default FormView;
