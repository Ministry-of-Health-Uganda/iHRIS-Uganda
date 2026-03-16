<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:CSD xmlns:csd="urn:ihe:iti:csd:2013">
      <csd:organizationDirectory/>
      <csd:serviceDirectory/>
      <csd:facilityDirectory/>
      <csd:providerDirectory>
	<xsl:for-each select="/csd:CSD/csd:providerDirectory/csd:provider">
	  <csd:provider>
	    <xsl:attribute name="entityID"><xsl:value-of select="./@entityID"/></xsl:attribute>
	    <csd:facilities>
	      <xsl:for-each select="csd:facilities/csd:facility">
		<csd:facility>
		  <xsl:attribute name='entityID'><xsl:value-of select="@entityID"/></xsl:attribute>
		  <xsl:attribute name="id"><xsl:value-of select="../../@entityID"/>/<xsl:value-of select="@entityID"/></xsl:attribute>
		</csd:facility>
	      </xsl:for-each>
	    </csd:facilities>	     
	  </csd:provider>
	</xsl:for-each>
      </csd:providerDirectory>
    </csd:CSD>
  </xsl:template>       
</xsl:stylesheet>
