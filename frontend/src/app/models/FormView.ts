export default class FormView<T = {[key: string]: FormView}> {
  public declare action: string;
  public declare attr: string[];
  public declare block_prefixes: string[];
  public declare disabled: boolean;
  public declare full_name: string;
  public declare help_attr: string[];
  public declare help_html: boolean;
  public declare id: string;
  public declare label_attr: string[];
  public declare label_html: boolean;
  public declare method: string;
  public declare name: string;
  public declare required: boolean;
  public declare unique_block_prefix: string;
  public declare value: string;
  public declare children: T;
}
