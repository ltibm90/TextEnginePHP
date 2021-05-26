<?php
abstract class TextElementFlags
{
	const TEF_NONE = 0;
	const TEF_ConditionalTag = 1;
	const TEF_NoAttributedTag = 2;
	const TEF_AutoClosedTag = 4;
        /// <summary>
        /// E.G [TAG=ATTRIB=test atrribnext/], returns: ATTRIB=test atrribnext
        /// </summary>
	const TEF_TagAttribonly =  8;
        /// <summary>
        /// if set [TAG/], tag not flagged autoclosed, if not set tag flagged autoclosed. 
        /// </summary>
	const TEF_DisableLastSlash  = 16;
		/// <summary>
        /// İşaretlenen tagın içeriğini ayrıştırmaz.
        /// </summary>
	const TEF_NoParse  = 32;
	const TEF_AutoCloseIfSameTagFound = 64;
	const TEF_PreventAutoCreation = 128;
	const TEF_NoParse_AllowParam = 256;
}