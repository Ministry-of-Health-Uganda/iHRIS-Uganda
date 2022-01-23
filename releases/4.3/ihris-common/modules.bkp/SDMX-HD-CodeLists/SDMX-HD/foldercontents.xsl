<?xml version='1.0'?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" />

<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>SDMX-HD v1.0 documentation</title>
		<meta http-equiv="Content-Type" content="text/xml; charset=iso-8859-1"/>
		<link rel="stylesheet" type="text/css" href="misc/tooltip.css" />
	</head>
     <script type="text/javascript" src="misc/tooltip.js"></script>
	<body style="color: Black; background-color: White; font-family: Arial, sans-serif; font-size: 10pt;">
	<div id="tooltip" style="width:400px;"></div>
		<h1 style="font-size: 18pt; letter-spacing: 2px;">SDMX-HD (Health Domain) v1.0 documentation<br />
            <span style="font-size: medium" >&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160;&#0160; 
            Version 2009.04.WHO.03</span></h1>
		<h2>Folder contents and data dictionnary</h2>
		<p>
      The SDMX-HD v1.0 specification is packaged as XML files
      following this structure (see <a href="GettingStarted.html">GettingStarted.html</a> for more information):</p>
	<xsl:for-each select="contents/folder">
<p>
	<b><xsl:value-of select="@relativePath"/>:</b>
	<ul>
		<xsl:apply-templates select="file"/>
	</ul>
</p>
	</xsl:for-each>
<p>&#0160;</p>
<p>&#0160;</p>
<p>&#0160;</p>
<p>&#0160;</p>
<p>&#0160;</p>
</body>
</html>
</xsl:template>


<xsl:template match="file">
	<li>
	<div style="width:600px;" onmouseout="tooltip.hide(this)">
		<xsl:attribute name="onmouseover">
			tooltip.show('<xsl:value-of select="sdmxInfo/@id"/>')
		</xsl:attribute>
		<a><xsl:attribute name="href">
			./<xsl:choose>
        <xsl:when test="../@relativePath=''"></xsl:when>
        <xsl:otherwise><xsl:value-of select="../@relativePath"/>/</xsl:otherwise>
      </xsl:choose><xsl:value-of select="@name"/>
		</xsl:attribute>
		<xsl:value-of select="@name"/></a>:&#0160;
		<xsl:value-of select="sdmxName"/>
	</div>
	<div style="display:none">
		<xsl:attribute name="id">
			<xsl:value-of select="sdmxInfo/@id"/>
		</xsl:attribute>
		<span><b><xsl:value-of select="@name"/></b>:<br/>
		<xsl:value-of select="sdmxDescription"/></span>
		<ul>
			<li>Identifier: <xsl:value-of select="sdmxInfo/@id"/></li>
			<li>Agency [version]: <xsl:value-of select="sdmxInfo/@agencyID"/>[<xsl:value-of select="sdmxInfo/@version"/>]</li>
			<li>IsFinal: <xsl:value-of select="sdmxInfo/@isFinal"/> (<xsl:choose>
				<xsl:when test="sdmxInfo[@isFinal='true']"><span style="color:red">read-only</span></xsl:when>
				<xsl:otherwise><span style="color:blue">can be modified</span></xsl:otherwise>
			</xsl:choose>)</li>
		</ul>
	</div>
	</li>
</xsl:template>
</xsl:stylesheet><!-- Stylus Studio meta-information - (c) 2004-2009. Progress Software Corporation. All rights reserved.

<metaInformation>
	<scenarios>
		<scenario default="yes" name="SDMX-foldercontents" userelativepaths="yes" externalpreview="no" url="foldercontents.xml" htmlbaseurl="" outputurl="" processortype="msxmldotnet" useresolver="no" profilemode="0" profiledepth="" profilelength=""
		          urlprofilexml="" commandline="" additionalpath="" additionalclasspath="" postprocessortype="none" postprocesscommandline="" postprocessadditionalpath="" postprocessgeneratedext="" validateoutput="yes" validator="internal"
		          customvalidator="">
			<advancedProp name="sInitialMode" value=""/>
			<advancedProp name="bXsltOneIsOkay" value="true"/>
			<advancedProp name="bSchemaAware" value="true"/>
			<advancedProp name="bXml11" value="false"/>
			<advancedProp name="iValidation" value="0"/>
			<advancedProp name="bExtensions" value="true"/>
			<advancedProp name="iWhitespace" value="0"/>
			<advancedProp name="sInitialTemplate" value=""/>
			<advancedProp name="bTinyTree" value="true"/>
			<advancedProp name="bWarnings" value="true"/>
			<advancedProp name="bUseDTD" value="false"/>
			<advancedProp name="iErrorHandling" value="fatal"/>
		</scenario>
	</scenarios>
	<MapperMetaTag>
		<MapperInfo srcSchemaPathIsRelative="yes" srcSchemaInterpretAsXML="no" destSchemaPath="" destSchemaRoot="" destSchemaPathIsRelative="yes" destSchemaInterpretAsXML="no">
			<SourceSchema srcSchemaPath="foldercontents.xml" srcSchemaRoot="contents" AssociatedInstance="" loaderFunction="document" loaderFunctionUsesURI="no"/>
		</MapperInfo>
		<MapperBlockPosition>
			<template match="/">
				<block path="html/body/xsl:for-each" x="144" y="72"/>
			</template>
		</MapperBlockPosition>
		<TemplateContext></TemplateContext>
		<MapperFilter side="source"></MapperFilter>
	</MapperMetaTag>
</metaInformation>
-->