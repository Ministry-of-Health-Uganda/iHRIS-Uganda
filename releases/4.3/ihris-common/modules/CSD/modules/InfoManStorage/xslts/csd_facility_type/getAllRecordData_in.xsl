<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:svs="urn:ihe:iti:svs:2008" 
  exclude-result-prefixes="svs xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     

  <xsl:template match="/">         
    <forms>
      <xsl:for-each select="//svs:Concept">
	<form name='csd_facility_type'>
	  <xsl:attribute name='id'><xsl:value-of select="@codeSystem"/>@@@<xsl:value-of select="@code"/></xsl:attribute>
	  <field name='codeSystem' type='STRING_LINE'><xsl:value-of select="@codeSystem"/></field>
	  <field name='code' type='STRING_LINE'><xsl:value-of select="@code"/></field>
	  <field name='name' type='STRING_LINE'><xsl:value-of select="@displayName"/></field>
	</form>
      </xsl:for-each>
    </forms>
  </xsl:template>
</xsl:stylesheet>
