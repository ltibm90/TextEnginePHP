<?php
abstract class IntertwinedBracketsStateType
{
	const IBST_NOT_ALLOWED = 0;
	const IBST_ALLOW_ALWAYS = 1;
	const IBST_ALLOW_NOATTRIBUTED_ONLY = 2;
	const IBST_ALLOW_NOATTRIBUTED_AND_PARAM = 3;
	const IBST_ALLOW_PARAM_ONLY = 4;
}