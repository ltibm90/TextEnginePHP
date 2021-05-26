<?php
abstract class SpecialCharType
{
        /// <summary>
        /// \ character disabled
        /// </summary>
	const SCT_NotAllowed = 1;
        /// <summary>
        /// e.g(\test, result: test)
        /// </summary>
	const SCT_AllowedAll = 2;
        /// <summary>
        /// e.g(\test\{} result: \test{ 
        /// </summary>
	const SCT_AllowedClosedTagOnly = 4;
	/// <summary>
	/// e.g{text}\{}{&text} result: {}
	/// </summary>
	const SCT_AllowedNoParseWithParamTagOnly = 8;
}