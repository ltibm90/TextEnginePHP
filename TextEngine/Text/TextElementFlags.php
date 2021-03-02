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
}