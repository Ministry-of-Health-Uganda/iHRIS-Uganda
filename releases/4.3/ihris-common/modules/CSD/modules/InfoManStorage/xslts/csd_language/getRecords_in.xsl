<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:svs="urn:ihe:iti:svs:2008" 
  exclude-result-prefixes="svs xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>

  <xsl:template match="svs:Concept">
    <svs:Concept id="{concat(@codeSystem,'@@@',@code)}">
      <xsl:apply-templates select="@*|node()"/>
    </svs:Concept>
  </xsl:template>
</xsl:stylesheet>
