# Query Syntax

## FQL Example

### Simple Equals

`array('field' => 123);`

This equals `FIELD = 123` in pseudo

### Simple operator
`array('field' => '=:123');`

This equals `FIELD = 123` in pseudo

### Complex operator
`array('field' => 'and:(or:(>:1 <:2) in:[1, 2, 3] range:[1, 3})');`

This equals `(field > 1 OR field < 2) AND field in (1,2,3) AND (field > 1 AND field <=3)` in psuedo

### ANY
`array('field' => '*');` 

This equals `field IS NOT NULL` in psuedo sql, or `= *` in FTS

### NULL
`array('field' => '~');`

This equals `field IS NULL` in psuedo sql, or `= ~` in FTS psuedo

### AND 
`&:()` or `and:()`

`.. AND .. AND ..` in psuedo

### OR
`|:()` or `or:()`

`.. OR .. OR .. ` in psuedo

### NOT
`!:()` or `not:()`

### in 
`in:()`

`IN ()` in psuedo sql, or `(eq OR eq OR eq ..)` in other

### text operator - match or wildcard
`array('field' => '%:*abc.');`

This equals `field LIKE %abc_` in  psuedo sql, or `*abc.` in FTS(FullTextSearch) psuedo

### text operator - not match or wildcard
`array('field' => '%-:*abc.');`

This equals `field NOT LIKE %abc_` in  psuedo sql, or `not *abc.` in FTS(FullTextSearch) psuedo

### text operator - contain
`array('field' => '%:foo');`

This equals `field LIKE %foo%` in psuedo sql  or `contain foo` in FTS(FullTextSearch) psuedo

### text operator - must contain
`array('field' => '%+:foo');`

This equals `field LIKE %foo%` in psuedo sql  or `must contain foo` in FTS(FullTextSearch) psuedo

### text operator - must not contain
`array('field' => '%-:foo');`

This equals `field NOT LIKE %foo%` in psuedo sql  or `not contain foo` in FTS(FullTextSearch) psuedo

### range
`range:[]` or `range:{}`

`{` or `}` include the following or forwarding value.
`[` or `]` exclude the following or forwarding value.

`( comp AND comp )` in psuedo

## Query Example

Quite similar with FQL, but prepend field `field:`

Equals operator will be `field:=:123`

Any or NULL match is simply `field:~` or `field:*`


## Operator Tables

| operator                  |                           |                                                      |
|:------------------------- |:------------------------- |:---------------------------------------------------- |
| =                         | Equal                     | Equal with following value                           |
| !=                        | Not Equal                 | Not Equal with following value                       |
| >                         | Greater Than              | Greater than following value                         |
| =>                        | Greater Than or Equal     | Equal to following value                             |
| <                         | Less Than                 | Less than following value                            |
| <=                        | Less Than or Equal        | Less than following value                            |
| %                         | Match or Contain          | Match wth or Contain following value                 |
| ~                         | Match Null                | Is null                                              |
| *                         | Match Any                 | Is not null                                          |
| ! or not                  | Not                       | Logical operator not of the following expr           |
| & or and                  | AND                       | Logical operator and of the following exprs          |
| \| or or                  | OR                        | Logical operator or of the following exprs           |
| in                        | IN                        | Equal with either one of the following values        |
| range                     | RANGE, between            | between the following values                         |



--------

[Back](./index.md)