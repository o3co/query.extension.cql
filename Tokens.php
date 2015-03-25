<?php
namespace O3Co\Query\Extension\CQL;

class Tokens 
{
	/* Token Types */
	// Token is a set of alphanumeric or charactors, which organize 
	const T_NONE                      = 1;
	const T_WHITESPACE                = 2;
	const T_ESCAPE                    = 3;
	const T_END                       = 4;
	// Identifier - commonly used for field identifier only.
	const T_IDENTIFIER                = 10;
	// Separator between field, operator, and condition
	const T_OPERATOR_SEPARATOR        = 11;
	const T_HIERARCHY_SEPARATOR       = 12;

	// Static Value
	const T_STRING                    = 31;
	const T_DECIMAL                   = 32;
	const T_BOOLEAN                   = 33;

	const T_QUOTE                     = 50;
	const T_SINGLE_QUOTE              = 51;
	const T_DOUBLE_QUOTE              = 52;

	// Operator Tokens
	const T_OPERATOR                  = 100;
	// Operator Tokens - Logical Expression
	const T_LOGICAL_OP                = 110;
	const T_OR                        = 111;
	const T_AND                       = 112;
	const T_NOT                       = 113;
	// Operator Tokens - Comparion Expression
	const T_COMPARISON_OP             = 120;
	const T_EQ                        = 121;
	const T_NE                        = 122;
	const T_GT                        = 123;
	const T_GE                        = 124;
	const T_LT                        = 125;
	const T_LE                        = 126;
	const T_MATCH                     = 127;
	// Operator Tokens - Comparison with NULL and ANY
	const T_IS_NULL                   = 131;
	const T_IS_ANY                    = 132;
	// Operator Tokens - Range Comparison
	const T_RANGE                     = 141;
	const T_IN                        = 142;

	// 
	const T_COLLECTION_BEGIN          = 201;
	const T_COLLECTION_END            = 202;
	const T_COLLECTION_SEPARATOR      = 203;

	//
	const T_COMPOSITE_BEGIN           = 211;
	const T_COMPOSITE_END             = 212;
	const T_COMPOSITE_SEPARATOR       = 213;

	// 
	const T_RANGE_GT                  = 221;
	const T_RANGE_GE                  = 222; 
	const T_RANGE_LT                  = 233;
	const T_RANGE_LE                  = 234;
	const T_RANGE_SEPARATOR           = 235;


	const T_SORT_ASC                  = 501;
	const T_SORT_DESC                 = 502;
}

