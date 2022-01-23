<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl">
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     

  <xsl:template match="/">
    <form name='csd_provider_name_search' id='0'>
      <field name='matches' type='ASSOC_MAP_RESULTS'>
	<xsl:for-each select="/csd:CSD/csd:providerDirectory/csd:provider">
	  <value keyform='csd_provider' >
	    <xsl:attribute name='keyid'><xsl:value-of select="@entityID"/></xsl:attribute>
	    <xsl:value-of select="csd:demographic/csd:name[1]/csd:forename/text()"/><xsl:text> </xsl:text>
	    <xsl:value-of select="csd:demographic/csd:name[1]/csd:surname/text()"/>
	  </value>
	</xsl:for-each>
      </field>
    </form>
  </xsl:template>         

</xsl:stylesheet>
