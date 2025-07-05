export default class FormView<T extends { [key: string]: FormView } = { [key: string]: FormView }> {
  public declare action: string;
  public declare attr: { [key: string]: string };
  public declare block_prefixes: string[];
  public declare disabled: boolean;
  public declare full_name: string;
  public declare help_attr: { [key: string]: string };
  public declare help_html: boolean;
  public declare id: string;
  public declare label: string;
  public declare label_attr: { [key: string]: string };
  public declare label_html: boolean;
  public declare method: string;
  public declare name: string;
  public declare required: boolean;
  public declare unique_block_prefix: string;
  public declare value: string;
  public declare children: T;
}
