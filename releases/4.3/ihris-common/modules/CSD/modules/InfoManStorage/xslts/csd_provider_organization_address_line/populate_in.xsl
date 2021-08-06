<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <xsl:for-each select="/csd:CSD/csd:providerDirectory/csd:provider/csd:demographic/csd:name/csd:otherNames">      
      <form name='csd_other_name' parent_form='csd_provider_name'>
	<xsl:attribute name="id"><xsl:value-of select="concat(../../../@entityID,'/',../@position,'/',@position)"/></xsl:attribute>
	<xsl:attribute name="parent_id"><xsl:value-of select="concat(../../../@entityID,'/',../@position)"/></xsl:attribute>
	<xsl:attribute name="created"><xsl:value-of select="translate(substring(../../../csd:record/@created,1,19),'T',' ')"/></xsl:attribute>
	<xsl:attribute name="modified"><xsl:value-of select="translate(substring(../../../csd:record/@updated,1,19),'T',' ')"/></xsl:attribute>
	<field name="name" type="STRING_LINE"><xsl:value-of select="text()"/></field> 
      </form>
    </xsl:for-each>
  </xsl:template> 
</xsl:stylesheet>
