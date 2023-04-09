# Review search query syntax

Reviews can be searched via specific filters:

- `id:<number>` finds the review with the given id.
- `state:open|closed` finds the reviews with the given state.
- `author:me|<name>|<email>` finds the reviews with the given author base on name or email.
- `reviewer:me|<name>|<email>` finds the reviews with the given reviewer base on name or email.
- `string` searches reviews with the given string in the title.

## Boolean operators

Boolean operators can be applied to include or exclude reviews.

- `AND` includes reviews that match both operands.
- `OR` includes reviews that match either operand.
- `NOT` excludes reviews that match the operand.

## Examples

```text
id:1234
```
```text
state:open and reviewer:"sherlock holmes"
```
```text
state:closed and (author:me or author:sherlock@example.com)
```
```text
T#1234 and author:watson
```
